<?php

namespace App\Services\Channels\Marketplaces;

use App\Services\Channels\AbstractDriver;
use Illuminate\Support\Facades\Http;

class TikTokShopDriver extends AbstractDriver
{
    private const BASE_URL = 'https://open-api.tiktokglobalshop.com';

    private function appKey(): string    { return $this->configOrCredential('services.tiktok_shop.app_key', 'app_key') ?? ''; }
    private function appSecret(): string { return $this->configOrCredential('services.tiktok_shop.app_secret', 'app_secret') ?? ''; }
    private function accessToken(): string { return $this->credentials()['access_token'] ?? ''; }
    private function shopCipher(): string  { return $this->credentials()['shop_cipher'] ?? ''; }

    /**
     * TikTok Shop API v2 HMAC-SHA256 signature.
     * Base string: {app_secret}{path}{sorted_non_reserved_params}{body_json}{app_secret}
     */
    private function sign(string $path, array $queryParams = [], string $body = ''): string
    {
        $reserved = ['app_key', 'shop_cipher', 'timestamp', 'sign', 'access_token'];
        $params   = array_filter($queryParams, fn($k) => ! in_array($k, $reserved, true), ARRAY_FILTER_USE_KEY);
        ksort($params);

        $paramStr = '';
        foreach ($params as $k => $v) {
            $paramStr .= $k . $v;
        }

        $base = $this->appSecret() . $path . $paramStr . $body . $this->appSecret();
        return strtoupper(hash_hmac('sha256', $base, $this->appSecret()));
    }

    private function commonParams(string $path, array $extra = []): array
    {
        $params = array_merge([
            'app_key'     => $this->appKey(),
            'timestamp'   => time(),
            'shop_cipher' => $this->shopCipher(),
        ], $extra);

        $params['sign'] = $this->sign($path, $params);

        return $params;
    }

    private function headers(): array
    {
        return ['x-tts-access-token' => $this->accessToken()];
    }

    public function getAuthUrl(): ?string
    {
        $appKey = $this->appKey();
        if (! $appKey) return null;

        return 'https://auth.tiktok-shops.com/oauth/authorize?' . http_build_query([
            'app_key' => $appKey,
            'state'   => $this->generateOAuthState(),
        ]);
    }

    public function handleOAuthCallback(array $params): void
    {
        $this->verifyOAuthState($params['state'] ?? '');

        $response = Http::post('https://auth.tiktok-shops.com/api/v2/token/get', [
            'app_key'    => $this->appKey(),
            'app_secret' => $this->appSecret(),
            'auth_code'  => $params['code'],
            'grant_type' => 'authorized_code',
        ]);

        if (! $response->successful() || $response->json('code') !== 0) {
            throw new \RuntimeException('TikTok Shop token exchange failed: ' . $response->body());
        }

        $data  = $response->json('data');
        $creds = $this->credentials();
        $creds['access_token']  = $data['access_token'];
        $creds['refresh_token'] = $data['refresh_token'] ?? '';
        $this->integration->credentials    = $creds;
        $this->integration->token_expires_at = now()->addSeconds($data['access_token_expire_in'] ?? 86400);
        $this->integration->save();
    }

    public function testConnection(): void
    {
        $path   = '/product/202309/products';
        $params = $this->commonParams($path, ['page_size' => 1]);

        $response = Http::withHeaders($this->headers())
            ->get(self::BASE_URL . $path, $params);

        if (! $response->successful()) {
            throw new \RuntimeException('TikTok Shop connection failed: ' . $response->status() . ' — ' . $response->body());
        }

        $code = $response->json('code', 0);
        if ($code !== 0) {
            throw new \RuntimeException('TikTok Shop error: ' . ($response->json('message') ?? $code));
        }
    }

    public function pushProduct(array $productData): string
    {
        $path = '/product/202309/products';

        $images = array_map(
            fn($url) => ['uri' => $url],
            array_slice(array_values(array_filter($productData['images'] ?? [])), 0, 9)
        );

        $body = json_encode(array_filter([
            'title'       => mb_substr($productData['title'] ?? '', 0, 255),
            'description' => $productData['description'] ?? '',
            'category_id' => '',       // TikTok Shop requires a category ID; left blank for manual assignment
            'main_images' => $images ?: [['uri' => '']],
            'skus'        => [[
                'sales_attributes' => [],
                'stock_infos'      => [[
                    'warehouse_id'   => '',
                    'available_stock' => (int) ($productData['stock'] ?? 0),
                ]],
                'seller_sku' => $productData['sku'] ?? '',
                'original_price' => number_format((float) ($productData['price'] ?? 0), 2, '.', ''),
            ]],
            'package_dimensions' => [
                'length' => '10', 'width' => '10', 'height' => '10', 'unit' => 'CENTIMETER',
            ],
            'package_weight'     => ['value' => '0.5', 'unit' => 'KILOGRAM'],
        ]));

        $params = $this->commonParams($path);
        $params['sign'] = $this->sign($path, $params, $body);

        $response = Http::withHeaders($this->headers())
            ->withBody($body, 'application/json')
            ->post(self::BASE_URL . $path . '?' . http_build_query($params));

        if (! $response->successful()) {
            throw new \RuntimeException('TikTok Shop pushProduct failed: ' . $response->status() . ' — ' . $response->body());
        }

        $code = $response->json('code', 0);
        if ($code !== 0) {
            throw new \RuntimeException('TikTok Shop error: ' . ($response->json('message') ?? $code));
        }

        return (string) $response->json('data.product_id', '');
    }
}
