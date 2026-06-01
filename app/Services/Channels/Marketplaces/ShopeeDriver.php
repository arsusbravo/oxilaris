<?php

namespace App\Services\Channels\Marketplaces;

use App\Services\Channels\AbstractDriver;
use Illuminate\Support\Facades\Http;

class ShopeeDriver extends AbstractDriver
{
    private const BASE_URL = 'https://partner.shopeemobile.com';

    private function partnerId(): int
    {
        return (int) ($this->configOrCredential('services.shopee.partner_id', 'partner_id') ?? 0);
    }

    private function partnerKey(): string
    {
        return $this->configOrCredential('services.shopee.partner_key', 'partner_key') ?? '';
    }

    private function shopId(): int
    {
        return (int) $this->credentials()['shop_id'];
    }

    private function accessToken(): string
    {
        return $this->credentials()['access_token'];
    }

    private function sign(string $path): array
    {
        $timestamp   = time();
        $accessToken = $this->accessToken();
        $shopId      = $this->shopId();
        $partnerId   = $this->partnerId();

        $base      = "{$partnerId}{$path}{$timestamp}{$accessToken}{$shopId}";
        $signature = hash_hmac('sha256', $base, $this->partnerKey());

        return [
            'partner_id'   => $partnerId,
            'timestamp'    => $timestamp,
            'access_token' => $accessToken,
            'shop_id'      => $shopId,
            'sign'         => $signature,
        ];
    }

    public function getAuthUrl(): ?string
    {
        $partnerId  = $this->partnerId();
        $partnerKey = $this->partnerKey();
        if (! $partnerId || ! $partnerKey) return null;

        $timestamp   = time();
        $callbackUrl = route('channels.callback', $this->integration);
        $state       = $this->generateOAuthState();
        $path        = '/api/v2/shop/auth_partner';
        $base        = $partnerId . $path . $timestamp;
        $sign        = hash_hmac('sha256', $base, $partnerKey);

        return self::BASE_URL . $path . '?' . http_build_query([
            'partner_id' => $partnerId,
            'timestamp'  => $timestamp,
            'sign'       => $sign,
            'redirect'   => $callbackUrl . '?state=' . $state,
        ]);
    }

    public function handleOAuthCallback(array $params): void
    {
        $this->verifyOAuthState($params['state'] ?? '');

        $partnerId  = $this->partnerId();
        $partnerKey = $this->partnerKey();
        $timestamp  = time();
        $path       = '/api/v2/auth/token/get';
        $sign       = hash_hmac('sha256', $partnerId . $path . $timestamp, $partnerKey);

        $response = Http::post(self::BASE_URL . $path, [
            'code'       => $params['code'],
            'shop_id'    => (int) ($params['shop_id'] ?? 0),
            'partner_id' => $partnerId,
            'timestamp'  => $timestamp,
            'sign'       => $sign,
        ]);

        if (! $response->successful() || ! empty($response->json('error'))) {
            throw new \RuntimeException('Shopee token exchange failed: ' . $response->body());
        }

        $creds = $this->credentials();
        $creds['access_token']  = $response->json('access_token');
        $creds['refresh_token'] = $response->json('refresh_token') ?? '';
        $creds['shop_id']       = (int) ($params['shop_id'] ?? 0);
        $this->integration->credentials    = $creds;
        $this->integration->token_expires_at = now()->addSeconds($response->json('expire_in', 14400));
        $this->integration->save();
    }

    public function testConnection(): void
    {
        $path     = '/api/v2/shop/get_shop_info';
        $response = Http::get(self::BASE_URL . $path, $this->sign($path));

        if (! $response->successful() || ! empty($response->json('error'))) {
            $err = $response->json('message') ?? $response->body();
            throw new \RuntimeException('Shopee connection failed: ' . $err);
        }
    }

    public function pushProduct(array $productData): string
    {
        $path = '/api/v2/product/add_item';

        $images = array_values(array_filter($productData['images'] ?? []));

        $payload = [
            'category_id'    => 100001,
            'original_price' => (float) ($productData['price'] ?? 0),
            'stock_info_v2'  => [
                'seller_stock' => [['stock' => (int) ($productData['stock'] ?? 0)]],
            ],
            'item_name'      => mb_substr($productData['title'], 0, 120),
            'description'    => mb_substr($productData['description'] ?? '', 0, 3000),
            'weight'         => 0.1,
            'item_sku'       => $productData['sku'] ?? '',
            'logistics'      => [['logistic_id' => 80003, 'enabled' => true]],
        ];

        if (! empty($images)) {
            $payload['image'] = ['image_url_list' => $images];
        }

        $response = Http::withQueryParameters($this->sign($path))
            ->post(self::BASE_URL . $path, $payload);

        if (! $response->successful() || ! empty($response->json('error'))) {
            $err = $response->json('message') ?? $response->body();
            throw new \RuntimeException('Shopee pushProduct failed: ' . $err);
        }

        return (string) $response->json('response.item_id', '');
    }
}
