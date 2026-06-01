<?php

namespace App\Services\Channels\Marketplaces;

use App\Services\Channels\AbstractDriver;
use Illuminate\Support\Facades\Http;

class OlxDriver extends AbstractDriver
{
    private const BASE_URL = 'https://api.olx.com';

    private function headers(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->credentials()['access_token'],
            'Accept'        => 'application/json',
        ];
    }

    private function categoryId(): int
    {
        return (int) ($this->credentials()['category_id'] ?? 3);
    }

    public function testConnection(): void
    {
        $response = Http::withHeaders($this->headers())
            ->get(self::BASE_URL . '/api/partner/accounts');

        if (! $response->successful()) {
            throw new \RuntimeException('OLX connection failed: ' . $response->status() . ' — ' . $response->body());
        }
    }

    public function pushProduct(array $productData): string
    {
        $images = array_map(fn($url) => ['url' => $url], array_slice($productData['images'] ?? [], 0, 8));

        $payload = [
            'title'       => mb_substr($productData['title'], 0, 70),
            'description' => $productData['description'] ?? '',
            'price'       => [
                'value'    => (int) round(($productData['price'] ?? 0) * 100),
                'currency' => 'IDR',
            ],
            'category'    => ['id' => $this->categoryId()],
            'contact'     => ['negotiable' => false],
        ];

        if (! empty($images)) {
            $payload['images'] = $images;
        }

        $response = Http::withHeaders($this->headers())
            ->post(self::BASE_URL . '/api/partner/adverts', $payload);

        if (! $response->successful()) {
            throw new \RuntimeException('OLX pushProduct failed: ' . $response->status() . ' — ' . $response->body());
        }

        return (string) ($response->json('id') ?? $response->json('data.id') ?? '');
    }
}
