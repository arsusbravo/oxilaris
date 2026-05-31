<?php

namespace App\Services\Channels\Stores;

use App\Services\Channels\AbstractDriver;
use Illuminate\Support\Facades\Http;

class CsCartDriver extends AbstractDriver
{
    private function baseUrl(): string
    {
        return rtrim($this->credentials()['store_url'], '/') . '/api';
    }

    private function auth(): array
    {
        return [
            $this->credentials()['api_email'],
            $this->credentials()['api_key'],
        ];
    }

    public function testConnection(): void
    {
        $response = Http::withBasicAuth(...$this->auth())
            ->get($this->baseUrl() . '/products', ['items_per_page' => 1]);

        if (! $response->successful()) {
            throw new \RuntimeException(
                'CS-Cart connection failed: ' . ($response->json('message') ?? $response->status())
            );
        }
    }

    public function fetchProducts(int $page = 1, int $perPage = 100): array
    {
        $response = Http::withBasicAuth(...$this->auth())
            ->get($this->baseUrl() . '/products', [
                'page'           => $page,
                'items_per_page' => $perPage,
                'status'         => 'A',
                'features'       => 'Y',
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException('CS-Cart fetchProducts failed: ' . $response->status());
        }

        $body = $response->json();
        // CS-Cart returns products as an object keyed by product_id; normalise to a list
        $products = array_values($body['products'] ?? []);

        if (! is_array($products)) {
            throw new \RuntimeException('CS-Cart returned an unexpected response.');
        }

        $baseUrl = $this->baseUrl();
        $auth    = $this->auth();
        $ids     = array_column($products, 'product_id');

        // Fetch individual product details concurrently (full URL + full description)
        $detailResponses = Http::pool(fn ($pool) =>
            array_map(fn ($id) =>
                $pool->withBasicAuth(...$auth)->get("{$baseUrl}/products/{$id}"),
                $ids
            )
        );

        foreach ($products as $i => &$product) {
            if (isset($detailResponses[$i]) && $detailResponses[$i]->successful()) {
                $detail = $detailResponses[$i]->json();
                $product['_product_url']      = $detail['product_url'] ?? null;
                $product['_full_description'] = $detail['full_description'] ?? $detail['short_description'] ?? null;
            }
        }
        unset($product);

        // Collect all unique category IDs across this page then resolve names concurrently
        $allCatIds = array_values(array_unique(array_merge(
            ...array_map(fn ($p) => array_values(array_filter((array) ($p['category_ids'] ?? []))), $products)
        )));

        $categoryNames = [];
        if (! empty($allCatIds)) {
            $catResponses = Http::pool(fn ($pool) =>
                array_map(fn ($id) =>
                    $pool->withBasicAuth(...$auth)->get("{$baseUrl}/categories/{$id}"),
                    $allCatIds
                )
            );
            foreach ($allCatIds as $i => $catId) {
                if (isset($catResponses[$i]) && $catResponses[$i]->successful()) {
                    $categoryNames[$catId] = $catResponses[$i]->json('category') ?? (string) $catId;
                } else {
                    $categoryNames[$catId] = (string) $catId;
                }
            }
        }

        return array_map(fn ($product) => $this->normalizeProduct($product, $categoryNames), $products);
    }

    private function buildProductUrl(array $item): string
    {
        $storeUrl = rtrim($this->credentials()['store_url'], '/');

        // Best: full path returned by individual product endpoint (includes category prefix)
        if (! empty($item['_product_url'])) {
            return $storeUrl . '/' . ltrim($item['_product_url'], '/');
        }

        // Fallback: dispatcher URL — always works on any CS-Cart installation
        return "{$storeUrl}/index.php?dispatch=products.view&product_id={$item['product_id']}";
    }

    private function extractCsCartFeatures(array $item): array
    {
        $attributes = [];
        foreach ($item['product_features'] ?? [] as $feature) {
            $name = $feature['description'] ?? ($feature['feature_name'] ?? null);
            if (! $name) continue;

            $values = [];
            foreach ($feature['variants'] ?? [] as $variant) {
                $label = $variant['variant'] ?? ($variant['value'] ?? null);
                if ($label !== null && $label !== '') {
                    $values[] = (string) $label;
                }
            }

            if (! empty($values)) {
                $attributes[] = ['name' => $name, 'values' => $values];
            }
        }

        return $attributes;
    }

    private function extractCsCartVariants(array $item): array
    {
        $variants = [];
        foreach ($item['combinations'] ?? [] as $combo) {
            $attrs = [];
            foreach ($combo['combination'] ?? [] as $option) {
                $optName  = $option['option_name'] ?? ($option['option_id'] ?? 'Option');
                $optValue = $option['variant_name'] ?? ($option['variant_id'] ?? '');
                $attrs[$optName] = $optValue;
            }
            $variants[] = [
                'external_id' => (string) ($combo['combination_id'] ?? uniqid()),
                'sku'         => $combo['combination_code'] ?? null,
                'price'       => (float) ($combo['price'] ?? $item['price'] ?? 0),
                'stock'       => (int) ($combo['amount'] ?? 0),
                'attributes'  => $attrs,
            ];
        }

        return $variants;
    }

    private function normalizeProduct(array $item, array $categoryNames = []): array
    {
        $image = $item['main_pair']['detailed']['image_path']
            ?? $item['main_pair']['icon']['image_path']
            ?? null;

        $rawCatIds = array_values(array_filter((array) ($item['category_ids'] ?? [])));
        $categories = array_map(fn ($id) => $categoryNames[$id] ?? (string) $id, $rawCatIds);

        return [
            'external_id'  => (string) $item['product_id'],
            'title'        => $item['product'] ?? '',
            'description'  => strip_tags($item['_full_description'] ?? $item['full_description'] ?? $item['short_description'] ?? ''),
            'price'        => (float) ($item['price'] ?? 0),
            'stock'        => (int) ($item['amount'] ?? 0),
            'sku'          => $item['product_code'] ?? null,
            'product_url'  => $this->buildProductUrl($item),
            'images'       => $image ? [$image] : [],
            'categories'   => $categories,
            'attributes'   => $this->extractCsCartFeatures($item),
            'variants'     => $this->extractCsCartVariants($item),
        ];
    }
}
