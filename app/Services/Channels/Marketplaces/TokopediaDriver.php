<?php

namespace App\Services\Channels\Marketplaces;

use App\Services\Channels\AbstractDriver;
use Illuminate\Support\Facades\Http;

class TokopediaDriver extends AbstractDriver
{
    private ?string $bearerToken = null;

    private function fsId(): string
    {
        return $this->credentials()['fs_id'];
    }

    private function shopId(): string
    {
        return $this->credentials()['shop_id'];
    }

    private function getToken(): string
    {
        if ($this->bearerToken) return $this->bearerToken;

        $creds = $this->credentials();
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode($creds['client_id'] . ':' . $creds['client_secret']),
        ])->asForm()->post('https://fs.tokopedia.net/token', [
            'grant_type' => 'client_credentials',
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException('Tokopedia auth failed: ' . $response->status() . ' — ' . $response->body());
        }

        $this->bearerToken = $response->json('access_token');
        return $this->bearerToken;
    }

    private function headers(): array
    {
        return ['Authorization' => 'Bearer ' . $this->getToken()];
    }

    public function testConnection(): void
    {
        $response = Http::withHeaders($this->headers())
            ->get("https://fs.tokopedia.net/v1/fs/{$this->fsId()}/shop/info", [
                'shop_id' => $this->shopId(),
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException('Tokopedia connection failed: ' . $response->status() . ' — ' . $response->body());
        }
    }

    public function pushProduct(array $productData): string
    {
        $images = array_map(fn($url) => ['url_list' => [$url]], array_slice($productData['images'] ?? [], 0, 5));

        $payload = [
            'name'        => mb_substr($productData['title'], 0, 255),
            'description' => $productData['description'] ?? '',
            'price'       => (int) ($productData['price'] ?? 0),
            'stock'       => (int) ($productData['stock'] ?? 0),
            'sku'         => $productData['sku'] ?? '',
            'weight'      => 100,
            'condition'   => 'NEW',
            'category_id' => 40000003,
            'images'      => $images ?: [['url_list' => []]],
        ];

        $response = Http::withHeaders($this->headers())
            ->post("https://fs.tokopedia.net/v2/fs/{$this->fsId()}/product/create", $payload);

        if (! $response->successful()) {
            throw new \RuntimeException('Tokopedia pushProduct failed: ' . $response->status() . ' — ' . $response->body());
        }

        return (string) $response->json('data.product_id', $response->json('product_id', ''));
    }
}
