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
        $creds = $this->credentials();
        $callbackUrl = route('channels.callback', $this->integration);

        return "https://{$this->shopDomain()}/admin/oauth/authorize?" . http_build_query([
            'client_id'    => $creds['client_id'],
            'scope'        => 'read_products',
            'redirect_uri' => $callbackUrl,
            'state'        => $this->integration->id,
        ]);
    }

    public function handleOAuthCallback(array $params): void
    {
        $creds = $this->credentials();

        $response = Http::post("https://{$this->shopDomain()}/admin/oauth/access_token", [
            'client_id'     => $creds['client_id'],
            'client_secret' => $creds['client_secret'],
            'code'          => $params['code'],
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException('Shopify OAuth token exchange failed: ' . $response->body());
        }

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
            'categories'   => [], // Shopify uses collections, not product categories
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
