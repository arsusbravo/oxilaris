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
        $currentPage = $page;

        $response = Http::withHeaders($this->headers())
            ->get($this->baseUrl() . '/products', [
                'searchCriteria[pageSize]'    => $perPage,
                'searchCriteria[currentPage]' => $currentPage,
                'searchCriteria[filter_groups][0][filters][0][field]'      => 'type_id',
                'searchCriteria[filter_groups][0][filters][0][value]'      => 'simple,configurable',
                'searchCriteria[filter_groups][0][filters][0][condition_type]' => 'in',
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException('Magento fetchProducts failed: ' . $response->status());
        }

        $items = $response->json('items', []);

        return array_map([$this, 'normalizeProduct'], $items);
    }

    public function pushProduct(array $productData): string
    {
        $sku = $productData['sku'] ?: 'MANUAL-' . strtolower(preg_replace('/[^a-z0-9]+/i', '-', $productData['title'])) . '-' . time();

        $payload = [
            'product' => [
                'sku'             => $sku,
                'name'            => $productData['title'],
                'price'           => (float) ($productData['price'] ?? 0),
                'status'          => 1,
                'type_id'         => 'simple',
                'attribute_set_id' => 4,
                'weight'          => 1,
                'custom_attributes' => [
                    ['attribute_code' => 'description', 'value' => $productData['description'] ?? ''],
                ],
                'extension_attributes' => [
                    'stock_item' => [
                        'qty'         => (int) ($productData['stock'] ?? 0),
                        'is_in_stock' => ($productData['stock'] ?? 0) > 0,
                    ],
                ],
            ],
        ];

        $response = Http::withHeaders($this->headers())
            ->post($this->baseUrl() . '/products', $payload);

        if (! $response->successful()) {
            throw new \RuntimeException('Magento pushProduct failed: ' . $response->status() . ' — ' . ($response->json('message') ?? $response->body()));
        }

        return (string) $response->json('id');
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

    private function normalizeProduct(array $item): array
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
            'categories'   => array_map(
                fn($link) => (string) ($link['category_id'] ?? $link),
                $item['extension_attributes']['category_links'] ?? []
            ),
            'attributes'   => $this->extractMagentoAttributes($item, $customAttributes),
            'variants'     => [], // Magento configurable children require separate API calls
        ];
    }
}
