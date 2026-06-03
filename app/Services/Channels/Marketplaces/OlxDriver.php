<?php

namespace App\Services\Channels\Marketplaces;

use App\Services\Channels\AbstractDriver;
use Illuminate\Support\Facades\Http;

class OlxDriver extends AbstractDriver
{
    // OLX Indonesia endpoints
    private const BASE_URL = 'https://api.olx.co.id';
    private const AUTH_URL = 'https://www.olx.co.id/oauth/authorize';
    private const TOKEN_URL = 'https://www.olx.co.id/oauth/token';

    private function clientId(): string
    {
        return $this->credentials()['client_id'] ?? '';
    }

    private function clientSecret(): string
    {
        return $this->credentials()['client_secret'] ?? '';
    }

    private function accessToken(): string
    {
        return $this->credentials()['access_token'] ?? '';
    }

    private function categoryId(): int
    {
        return (int) ($this->credentials()['category_id'] ?? 3);
    }

    private function headers(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->accessToken(),
            'Accept'        => 'application/json',
        ];
    }

    public function getAuthUrl(): ?string
    {
        if (! $this->clientId()) return null;

        return self::AUTH_URL . '?' . http_build_query([
            'client_id'     => $this->clientId(),
            'response_type' => 'code',
            'redirect_uri'  => route('channels.callback', $this->integration),
            'scope'         => 'v2 ads:manage:self',
            'state'         => $this->generateOAuthState(),
        ]);
    }

    public function handleOAuthCallback(array $params): void
    {
        $this->verifyOAuthState($params['state'] ?? '');

        if (empty($params['code'])) {
            throw new \RuntimeException('OLX OAuth: Missing authorization code');
        }

        $response = Http::post(self::TOKEN_URL, [
            'grant_type'    => 'authorization_code',
            'code'          => $params['code'],
            'client_id'     => $this->clientId(),
            'client_secret' => $this->clientSecret(),
            'redirect_uri'  => route('channels.callback', $this->integration),
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException('OLX token exchange failed: ' . $response->status() . ' — ' . $response->body());
        }

        $data = $response->json();
        if (empty($data['access_token'])) {
            throw new \RuntimeException('OLX token exchange: No access token in response');
        }

        $creds = $this->credentials();
        $creds['access_token'] = $data['access_token'];
        $this->integration->credentials = $creds;
        $this->integration->token_expires_at = now()->addSeconds($data['expires_in'] ?? 3600);
        $this->integration->save();
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
