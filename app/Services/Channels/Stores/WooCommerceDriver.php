<?php

namespace App\Services\Channels\Stores;

use App\Services\Channels\AbstractDriver;
use Illuminate\Support\Facades\Http;

class WooCommerceDriver extends AbstractDriver
{
    private function baseUrl(): string
    {
        $url = rtrim($this->credentials()['site_url'], '/');
        return "{$url}/wp-json/wc/v3";
    }

    private function auth(): array
    {
        return [
            $this->credentials()['consumer_key'],
            $this->credentials()['consumer_secret'],
        ];
    }

    public function testConnection(): void
    {
        $response = Http::withBasicAuth(...$this->auth())
            ->get($this->baseUrl() . '/system_status');

        if (str_contains($response->header('Content-Type') ?? '', 'text/html')) {
            throw new \RuntimeException(
                'WooCommerce is not returning API data. Go to WordPress Admin → Settings → Permalinks, select "Post name", and save.'
            );
        }

        if (! $response->successful()) {
            throw new \RuntimeException('WooCommerce connection failed: ' . $response->status());
        }
    }

    public function fetchProducts(int $page = 1, int $perPage = 100): array
    {
        $response = Http::withBasicAuth(...$this->auth())
            ->get($this->baseUrl() . '/products', [
                'per_page' => $perPage,
                'page' => $page,
                'status' => 'publish',
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException('WooCommerce fetchProducts failed: ' . $response->status());
        }

        $products = $response->json();

        if (! is_array($products)) {
            if (str_contains($response->header('Content-Type') ?? '', 'text/html')) {
                throw new \RuntimeException(
                    'WooCommerce is not returning API data. This is almost always caused by WordPress using "Plain" permalinks. ' .
                    'Ask the store owner to go to WordPress Admin → Settings → Permalinks and select "Post name", then save.'
                );
            }
            throw new \RuntimeException('WooCommerce returned an unexpected response (not JSON). Status: ' . $response->status());
        }

        // Fetch full variation data for variable products (WooCommerce only returns IDs inline)
        foreach ($products as &$product) {
            if (($product['type'] ?? '') === 'variable' && ! empty($product['variations'])) {
                $varResp = Http::withBasicAuth(...$this->auth())
                    ->get($this->baseUrl() . "/products/{$product['id']}/variations", ['per_page' => 100]);
                $product['_variations'] = $varResp->successful() ? $varResp->json() : [];
            }
        }
        unset($product);

        return array_map([$this, 'normalizeProduct'], $products);
    }

    private function normalizeProduct(array $item): array
    {
        return [
            'external_id'  => (string) $item['id'],
            'title'        => $item['name'] ?? '',
            'description'  => strip_tags($item['description'] ?? ''),
            'price'        => (float) ($item['price'] ?? 0),
            'stock'        => (int) ($item['stock_quantity'] ?? 0),
            'sku'          => $item['sku'] ?? null,
            'images'       => array_map(fn($img) => $img['src'], $item['images'] ?? []),
            'categories'   => array_map(fn($cat) => $cat['name'], $item['categories'] ?? []),
            'attributes'   => array_map(fn($attr) => [
                'name' => $attr['name'],
                'values' => $attr['options'] ?? [],
            ], $item['attributes'] ?? []),
            'variants'     => array_map(fn($v) => [
                'external_id' => (string) $v['id'],
                'sku'         => $v['sku'] ?? null,
                'price'       => (float) ($v['price'] ?? 0),
                'stock'       => (int) ($v['stock_quantity'] ?? 0),
                'attributes'  => array_reduce(
                    $v['attributes'] ?? [],
                    fn($carry, $a) => array_merge($carry, [$a['name'] => $a['option']]),
                    []
                ),
            ], $item['_variations'] ?? []),
        ];
    }
}
