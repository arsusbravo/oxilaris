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

        // Simple / virtual products: pull readable spec attributes from custom_attributes
        $specCodes = ['color', 'size', 'material', 'pattern', 'weight', 'width', 'height', 'length', 'gender', 'age_group'];
        $attributes = [];
        foreach ($item['custom_attributes'] ?? [] as $attr) {
            if (in_array($attr['attribute_code'], $specCodes) && $attr['value'] !== '' && $attr['value'] !== null) {
                $attributes[] = [
                    'name'   => ucwords(str_replace('_', ' ', $attr['attribute_code'])),
                    'values' => [$attr['value']],
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
