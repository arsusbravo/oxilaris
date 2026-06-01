<?php

namespace App\Services\Channels\Stores;

use App\Services\Channels\AbstractDriver;
use Illuminate\Support\Facades\Http;

class MagentoDriver extends AbstractDriver
{
    private function baseUrl(): string
    {
        return rtrim($this->credentials()['base_url'], '/') . '/rest/V1';
    }

    private function headers(): array
    {
        return ['Authorization' => 'Bearer ' . $this->credentials()['access_token']];
    }

    public function testConnection(): void
    {
        $response = Http::withHeaders($this->headers())
            ->get($this->baseUrl() . '/products', [
                'searchCriteria[pageSize]'    => 1,
                'searchCriteria[currentPage]' => 1,
            ]);

        if (! $response->successful()) {
            $body = $response->json('message') ?? $response->body();
            throw new \RuntimeException('Magento connection failed: ' . $response->status() . ' — ' . mb_substr($body, 0, 300));
        }
    }

    public function fetchProducts(int $page = 1, int $perPage = 100): array
    {
        $categoryMap = $this->fetchCategoryMap();

        $response = Http::withHeaders($this->headers())
            ->get($this->baseUrl() . '/products', [
                'searchCriteria[pageSize]'    => $perPage,
                'searchCriteria[currentPage]' => $page,
                'searchCriteria[filter_groups][0][filters][0][field]'          => 'type_id',
                'searchCriteria[filter_groups][0][filters][0][value]'          => 'simple,configurable',
                'searchCriteria[filter_groups][0][filters][0][condition_type]' => 'in',
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException('Magento fetchProducts failed: ' . $response->status());
        }

        return array_map(
            fn($item) => $this->normalizeProduct($item, $categoryMap),
            $response->json('items', [])
        );
    }

    private function fetchCategoryMap(): array
    {
        $response = Http::withHeaders($this->headers())
            ->get($this->baseUrl() . '/categories', ['rootCategoryId' => 1]);

        if (! $response->successful()) {
            return [];
        }

        $map = [];
        if ($id = $response->json('id')) {
            $map[(int) $id] = $response->json('name', '');
        }
        $this->flattenCategoryTree($response->json('children_data', []), $map);

        return $map;
    }

    private function flattenCategoryTree(array $nodes, array &$map): void
    {
        foreach ($nodes as $node) {
            $map[(int) $node['id']] = $node['name'];
            if (! empty($node['children_data'])) {
                $this->flattenCategoryTree($node['children_data'], $map);
            }
        }
    }

    public function pushProduct(array $productData): string
    {
        $sku = $productData['sku'] ?: 'MANUAL-' . strtolower(preg_replace('/[^a-z0-9]+/i', '-', $productData['title'])) . '-' . time();

        // Build custom_attributes: description + any product specs
        $customAttrs = [
            ['attribute_code' => 'description', 'value' => $productData['description'] ?? ''],
        ];
        foreach ($productData['attributes'] ?? [] as $attr) {
            $code = strtolower(preg_replace('/[^a-z0-9]+/', '_', trim($attr['name'] ?? '')));
            if ($code !== '' && ! empty($attr['values'])) {
                $customAttrs[] = [
                    'attribute_code' => $code,
                    'value'          => implode(', ', (array) $attr['values']),
                ];
            }
        }

        // Resolve category names → IDs for category_links
        $categoryLinks = $this->resolveCategoryLinks($productData['categories'] ?? []);

        $payload = [
            'product' => [
                'sku'              => $sku,
                'name'             => $productData['title'],
                'price'            => (float) ($productData['price'] ?? 0),
                'status'           => 1,
                'type_id'          => 'simple',
                'attribute_set_id' => 4,
                'weight'           => 1,
                'custom_attributes'    => $customAttrs,
                'extension_attributes' => [
                    'stock_item'     => [
                        'qty'         => (int) ($productData['stock'] ?? 0),
                        'is_in_stock' => ($productData['stock'] ?? 0) > 0,
                    ],
                    'category_links' => $categoryLinks,
                ],
            ],
        ];

        $response = Http::withHeaders($this->headers())
            ->post($this->baseUrl() . '/products', $payload);

        if (! $response->successful()) {
            throw new \RuntimeException('Magento pushProduct failed: ' . $response->status() . ' — ' . ($response->json('message') ?? $response->body()));
        }

        $savedSku = $response->json('sku') ?? $sku;

        // Upload images — Magento requires base64-encoded content via media endpoint
        foreach (array_values(array_filter($productData['images'] ?? [])) as $i => $imageUrl) {
            $this->pushImage($savedSku, $imageUrl, $i === 0);
        }

        return (string) $response->json('id');
    }

    private function resolveCategoryLinks(array $names): array
    {
        if (empty($names)) {
            return [];
        }

        $categoryMap = $this->fetchCategoryMap(); // [id => name]
        $nameToId    = [];
        foreach ($categoryMap as $id => $name) {
            $nameToId[strtolower((string) $name)] = $id;
        }

        $links = [];
        foreach ($names as $name) {
            $id = $nameToId[strtolower((string) $name)] ?? null;
            if ($id !== null) {
                $links[] = ['category_id' => (string) $id, 'position' => 0];
            }
        }

        return $links;
    }

    private function pushImage(string $sku, string $imageUrl, bool $isBase): void
    {
        try {
            $imgResponse = Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])
                ->timeout(15)
                ->get($imageUrl);

            if (! $imgResponse->successful()) {
                return;
            }

            $mime = strtok($imgResponse->header('Content-Type') ?: 'image/jpeg', ';');
            $ext  = match ($mime) {
                'image/png'  => 'png',
                'image/gif'  => 'gif',
                'image/webp' => 'webp',
                default      => 'jpg',
            };

            Http::withHeaders($this->headers())
                ->post($this->baseUrl() . "/products/{$sku}/media", [
                    'entry' => [
                        'media_type' => 'image',
                        'label'      => '',
                        'position'   => 0,
                        'disabled'   => false,
                        'types'      => $isBase ? ['image', 'small_image', 'thumbnail'] : [],
                        'content'    => [
                            'base64_encoded_data' => base64_encode($imgResponse->body()),
                            'type'                => $mime,
                            'name'                => basename(parse_url($imageUrl, PHP_URL_PATH)) ?: "product.{$ext}",
                        ],
                    ],
                ]);
        } catch (\Throwable) {
            // Don't fail the entire push if one image can't be uploaded
        }
    }

    private function extractMagentoAttributes(array $item, array $customAttributes): array
    {
        // Configurable products expose their axes (Color, Size) in configurable_product_options
        $configurableOptions = $item['extension_attributes']['configurable_product_options'] ?? [];
        if (! empty($configurableOptions)) {
            return array_map(fn($opt) => [
                'name'   => $opt['label'],
                'values' => array_column($opt['values'] ?? [], 'value_index'),
            ], $configurableOptions);
        }

        // Simple / virtual products: pull readable spec attributes from the pre-parsed flat map
        $specCodes = ['color', 'size', 'material', 'pattern', 'weight', 'width', 'height', 'length', 'gender', 'age_group'];
        $attributes = [];
        foreach ($specCodes as $code) {
            $value = $customAttributes[$code] ?? null;
            if ($value !== null && $value !== '') {
                $attributes[] = [
                    'name'   => ucwords(str_replace('_', ' ', $code)),
                    'values' => [(string) $value],
                ];
            }
        }

        return $attributes;
    }

    private function normalizeProduct(array $item, array $categoryMap = []): array
    {
        $customAttributes = [];
        foreach ($item['custom_attributes'] ?? [] as $attr) {
            $customAttributes[$attr['attribute_code']] = $attr['value'];
        }

        $mediaGallery = array_filter(
            $item['media_gallery_entries'] ?? [],
            fn($e) => in_array('image', $e['types'] ?? []) || $e['media_type'] === 'image'
        );

        return [
            'external_id'  => (string) $item['id'],
            'title'        => $item['name'] ?? '',
            'description'  => strip_tags($customAttributes['description'] ?? ''),
            'price'        => (float) ($item['price'] ?? 0),
            'stock'        => (int) ($item['extension_attributes']['stock_item']['qty'] ?? 0),
            'sku'          => $item['sku'] ?? null,
            'product_url'  => isset($customAttributes['url_key'])
                ? rtrim($this->credentials()['base_url'], '/') . '/' . $customAttributes['url_key'] . '.html'
                : null,
            'images'       => array_map(
                fn($e) => rtrim($this->credentials()['base_url'], '/') . '/pub/media/catalog/product' . $e['file'],
                array_values($mediaGallery)
            ),
            'categories'   => array_values(array_filter(array_map(
                fn($link) => $categoryMap[(int) ($link['category_id'] ?? 0)] ?? null,
                $item['extension_attributes']['category_links'] ?? []
            ))),
            'attributes'   => $this->extractMagentoAttributes($item, $customAttributes),
            'variants'     => [], // Magento configurable children require separate API calls
        ];
    }
}
