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

    public function getAuthUrl(): ?string
    {
        $appName = config('services.woocommerce.app_name') ?: config('app.name');
        if (! $appName) return null;

        $siteUrl     = rtrim($this->credentials()['site_url'] ?? '', '/');
        if (! $siteUrl) return null;

        $callbackUrl = config('services.woocommerce.callback_url')
            ?: route('channels.callback', $this->integration);

        return $siteUrl . '/wc-auth/v1/authorize?' . http_build_query([
            'app_name'     => $appName,
            'scope'        => 'read_write',
            'user_id'      => $this->integration->id,
            'return_url'   => route('channels.show', $this->integration),
            'callback_url' => $callbackUrl . '?state=' . $this->generateOAuthState(),
        ]);
    }

    public function handleOAuthCallback(array $params): void
    {
        $this->verifyOAuthState($params['state'] ?? '');

        $creds = $this->credentials();
        $creds['consumer_key']    = $params['consumer_key']    ?? '';
        $creds['consumer_secret'] = $params['consumer_secret'] ?? '';
        $this->integration->credentials = $creds;
        $this->integration->save();
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
        $categoryMap = $this->fetchCategoryMap();

        $response = Http::withBasicAuth(...$this->auth())
            ->get($this->baseUrl() . '/products', [
                'per_page' => $perPage,
                'page'     => $page,
                'status'   => 'publish',
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

        return array_map(fn($p) => $this->normalizeProduct($p, $categoryMap), $products);
    }

    private function fetchCategoryMap(): array
    {
        $map  = [];
        $page = 1;

        do {
            $response = Http::withBasicAuth(...$this->auth())
                ->get($this->baseUrl() . '/products/categories', [
                    'per_page' => 100,
                    'page'     => $page,
                    'orderby'  => 'id',
                    'order'    => 'asc',
                ]);

            if (! $response->successful()) {
                break;
            }

            $batch = $response->json() ?? [];
            foreach ($batch as $cat) {
                if (isset($cat['id'], $cat['name'])) {
                    $map[(int) $cat['id']] = $cat['name'];
                }
            }

            $totalPages = (int) ($response->header('X-WP-TotalPages') ?: 1);
            $page++;
        } while ($page <= $totalPages && ! empty($batch));

        return $map;
    }

    public function pushProduct(array $productData): string
    {
        $payload = [
            'name'           => $productData['title'],
            'description'    => $productData['description'] ?? '',
            'regular_price'  => (string) ($productData['price'] ?? '0'),
            'manage_stock'   => true,
            'stock_quantity' => (int) ($productData['stock'] ?? 0),
            'sku'            => $productData['sku'] ?? '',
            'status'         => 'publish',
            'images'         => array_map(fn($url) => ['src' => $url], $productData['images'] ?? []),
            'categories'     => array_map(
                fn($cat) => ['name' => $cat],
                array_filter(
                    $productData['categories'] ?? [],
                    fn($cat) => is_string($cat) && $cat !== '' && ! filter_var($cat, FILTER_VALIDATE_URL)
                )
            ),
            'attributes'     => array_map(fn($attr) => [
                'name'    => $attr['name'],
                'options' => $attr['values'] ?? [],
                'visible' => true,
            ], $productData['attributes'] ?? []),
        ];

        $response = Http::withBasicAuth(...$this->auth())
            ->post($this->baseUrl() . '/products', $payload);

        if (! $response->successful()) {
            throw new \RuntimeException('WooCommerce pushProduct failed: ' . $response->status() . ' — ' . $response->body());
        }

        return (string) $response->json('id');
    }

    private function normalizeProduct(array $item, array $categoryMap = []): array
    {
        return [
            'external_id'  => (string) $item['id'],
            'title'        => $item['name'] ?? '',
            'description'  => strip_tags($item['description'] ?? ''),
            'price'        => (float) ($item['price'] ?? 0),
            'stock'        => (int) ($item['stock_quantity'] ?? 0),
            'sku'          => $item['sku'] ?? null,
            'product_url'  => $item['permalink'] ?? null,
            'images'       => array_values(array_filter(
                array_map(fn($img) => $img['src'] ?? '', $item['images'] ?? []),
                fn($src) => $src !== '' && ! str_contains(strtolower($src), 'placeholder')
            )),
            'categories'   => array_values(array_filter(array_map(
                fn($cat) => $categoryMap[(int)($cat['id'] ?? 0)]
                    ?? (! filter_var($cat['name'] ?? '', FILTER_VALIDATE_URL) ? ($cat['name'] ?? null) : null),
                $item['categories'] ?? []
            ))),
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
