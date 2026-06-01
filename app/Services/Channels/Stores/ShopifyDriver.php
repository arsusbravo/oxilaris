<?php

namespace App\Services\Channels\Stores;

use App\Services\Channels\AbstractDriver;
use Illuminate\Support\Facades\Http;

class ShopifyDriver extends AbstractDriver
{
    private function shopDomain(): string
    {
        $domain = $this->credentials()['shop_domain'] ?? '';
        return rtrim(preg_replace('#^https?://#', '', $domain), '/');
    }

    private function baseUrl(): string
    {
        return "https://{$this->shopDomain()}/admin/api/2024-01";
    }

    private function headers(): array
    {
        return ['X-Shopify-Access-Token' => $this->credentials()['access_token']];
    }

    public function getAuthUrl(): ?string
    {
        $clientId    = $this->configOrCredential('services.shopify.client_id', 'client_id');
        $callbackUrl = route('channels.callback', $this->integration);

        return "https://{$this->shopDomain()}/admin/oauth/authorize?" . http_build_query([
            'client_id'    => $clientId,
            'scope'        => 'read_products,write_products,read_inventory,write_inventory',
            'redirect_uri' => $callbackUrl,
            'state'        => $this->generateOAuthState(),
        ]);
    }

    public function handleOAuthCallback(array $params): void
    {
        $this->verifyOAuthState($params['state'] ?? '');

        $clientId     = $this->configOrCredential('services.shopify.client_id', 'client_id');
        $clientSecret = $this->configOrCredential('services.shopify.client_secret', 'client_secret');

        $response = Http::post("https://{$this->shopDomain()}/admin/oauth/access_token", [
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'code'          => $params['code'],
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException('Shopify OAuth token exchange failed: ' . $response->body());
        }

        $creds = $this->credentials();
        $creds['access_token'] = $response->json('access_token');
        $this->integration->credentials = $creds;
        $this->integration->save();
    }

    public function testConnection(): void
    {
        $response = Http::withHeaders($this->headers())
            ->get($this->baseUrl() . '/shop.json');

        if (! $response->successful()) {
            throw new \RuntimeException('Shopify connection failed: ' . $response->status());
        }
    }

    public function fetchProducts(int $page = 1, int $perPage = 100): array
    {
        $response = Http::withHeaders($this->headers())
            ->get($this->baseUrl() . '/products.json', [
                'limit' => min($perPage, 250),
                'page_info' => null, // cursor-based pagination handled separately for large stores
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException('Shopify fetchProducts failed: ' . $response->status());
        }

        return array_map([$this, 'normalizeProduct'], $response->json('products', []));
    }

    public function pushProduct(array $productData): string
    {
        $categories = array_values(array_filter($productData['categories'] ?? []));

        $payload = [
            'product' => [
                'title'        => $productData['title'],
                'body_html'    => $productData['description'] ?? '',
                'product_type' => $categories[0] ?? '',
                'tags'         => implode(', ', array_slice($categories, 1)), // extra categories as tags
                'status'       => 'active',
                'variants'     => [[
                    'price'                => (string) ($productData['price'] ?? '0'),
                    'sku'                  => $productData['sku'] ?? '',
                    'inventory_quantity'   => (int) ($productData['stock'] ?? 0),
                    'inventory_management' => 'shopify',
                ]],
                'images'  => array_map(fn($url) => ['src' => $url], array_filter($productData['images'] ?? [])),
                'options' => array_map(fn($attr) => [
                    'name'   => $attr['name'],
                    'values' => array_values(array_filter((array) ($attr['values'] ?? []))),
                ], array_filter($productData['attributes'] ?? [], fn($a) => ! empty($a['name']))),
            ],
        ];

        $response = Http::withHeaders($this->headers())
            ->post($this->baseUrl() . '/products.json', $payload);

        if (! $response->successful()) {
            throw new \RuntimeException('Shopify pushProduct failed: ' . $response->status() . ' — ' . $response->body());
        }

        return (string) $response->json('product.id');
    }

    private function normalizeProduct(array $item): array
    {
        $variants = $item['variants'] ?? [];
        $firstVariant = $variants[0] ?? [];

        return [
            'external_id'  => (string) $item['id'],
            'title'        => $item['title'] ?? '',
            'description'  => strip_tags($item['body_html'] ?? ''),
            'price'        => (float) ($firstVariant['price'] ?? 0),
            'stock'        => array_sum(array_column($variants, 'inventory_quantity')),
            'sku'          => $firstVariant['sku'] ?? null,
            'product_url'  => isset($item['handle']) ? "https://{$this->shopDomain()}/products/{$item['handle']}" : null,
            'images'       => array_map(fn($img) => $img['src'], $item['images'] ?? []),
            'categories'   => array_values(array_filter([trim($item['product_type'] ?? '')])),
            'attributes'   => array_map(fn($opt) => [
                'name' => $opt['name'],
                'values' => $opt['values'],
            ], $item['options'] ?? []),
            'variants'     => array_map(function ($v) use ($item) {
                $optionNames = array_column($item['options'] ?? [], 'name');
                $attrs = [];
                foreach (['option1', 'option2', 'option3'] as $i => $key) {
                    if (isset($v[$key], $optionNames[$i]) && $v[$key] !== 'Default Title') {
                        $attrs[$optionNames[$i]] = $v[$key];
                    }
                }
                return [
                    'external_id' => (string) $v['id'],
                    'sku'         => $v['sku'] ?? null,
                    'price'       => (float) ($v['price'] ?? 0),
                    'stock'       => (int) ($v['inventory_quantity'] ?? 0),
                    'attributes'  => $attrs,
                ];
            }, $variants),
        ];
    }
}
