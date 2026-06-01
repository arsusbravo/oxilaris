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

                // Detail endpoint returns reliable category_ids; list endpoint often omits them
                $detailCatIds = $detail['category_ids'] ?? null;
                if (! empty($detailCatIds)) {
                    $product['category_ids'] = (array) $detailCatIds;
                } elseif (! empty($detail['main_category'])) {
                    $product['category_ids'] = [$detail['main_category']];
                }
            }
        }
        unset($product);

        // Collect all unique category IDs across this page then resolve names concurrently
        // Normalise every ID to a string to avoid int/string key mismatches
        $allCatIds = array_values(array_unique(array_merge(
            ...array_map(
                fn ($p) => array_values(array_filter(array_map('strval', (array) ($p['category_ids'] ?? [])))),
                $products
            )
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
                $key = (string) $catId;
                if (isset($catResponses[$i]) && $catResponses[$i]->successful()) {
                    $data = $catResponses[$i]->json();
                    // CS-Cart may return the name under 'category' (string) or inside a keyed wrapper
                    $name = is_string($data['category'] ?? null)
                        ? $data['category']
                        : ($data[$catId]['category'] ?? ($data[(int)$catId]['category'] ?? null));
                    $categoryNames[$key] = $name ?? $key;
                } else {
                    $categoryNames[$key] = $key;
                }
            }
        }

        return array_map(fn ($product) => $this->normalizeProduct($product, $categoryNames), $products);
    }

    public function pushProduct(array $productData): string
    {
        $images = array_values(array_filter($productData['images'] ?? []));

        $payload = [
            'product'          => $productData['title'],
            'full_description' => $productData['description'] ?? '',
            'price'            => (float) ($productData['price'] ?? 0),
            'amount'           => (int) ($productData['stock'] ?? 0),
            'product_code'     => $productData['sku'] ?? '',
            'status'           => 'A',
        ];

        // Main image — CS-Cart fetches and stores the image from the URL
        if (! empty($images)) {
            $payload['main_pair'] = [
                'detailed' => ['http_image_path' => $images[0]],
                'icon'     => ['http_image_path' => $images[0]],
            ];
        }

        // Resolve category names → IDs and attach
        $catIds = $this->resolveCategoryIds($productData['categories'] ?? []);
        if (! empty($catIds)) {
            $payload['main_category'] = $catIds[0];
            $payload['category_ids']  = $catIds;
        }

        $response = Http::withBasicAuth(...$this->auth())
            ->post($this->baseUrl() . '/products', $payload);

        if (! $response->successful()) {
            throw new \RuntimeException('CS-Cart pushProduct failed: ' . $response->status() . ' — ' . $response->body());
        }

        $productId = (string) $response->json('product_id');

        // Additional images (CS-Cart supports image pairs per product)
        foreach (array_slice($images, 1) as $imageUrl) {
            Http::withBasicAuth(...$this->auth())
                ->post($this->baseUrl() . "/images/{$productId}", [
                    'object'    => 'product',
                    'object_id' => $productId,
                    'detailed'  => ['http_image_path' => $imageUrl],
                    'icon'      => ['http_image_path' => $imageUrl],
                ]);
        }

        return $productId;
    }

    private function resolveCategoryIds(array $names): array
    {
        if (empty($names)) {
            return [];
        }

        $response = Http::withBasicAuth(...$this->auth())
            ->get($this->baseUrl() . '/categories', ['items_per_page' => 500]);

        if (! $response->successful()) {
            return [];
        }

        $nameToId = [];
        foreach ($response->json('categories', []) as $cat) {
            if (isset($cat['category'], $cat['category_id'])) {
                $nameToId[strtolower((string) $cat['category'])] = (int) $cat['category_id'];
            }
        }

        $ids = [];
        foreach ($names as $name) {
            $id = $nameToId[strtolower((string) $name)] ?? null;
            if ($id !== null) {
                $ids[] = $id;
            }
        }

        return $ids;
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

        $rawCatIds  = array_values(array_filter(array_map('strval', (array) ($item['category_ids'] ?? []))));
        $categories = array_values(array_filter(array_map(
            fn ($id) => isset($categoryNames[$id]) && $categoryNames[$id] !== $id ? $categoryNames[$id] : null,
            $rawCatIds
        )));

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
