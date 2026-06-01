<?php

namespace App\Services\Channels;

use App\Models\ChannelIntegration;
use App\Services\Channels\Contracts\ChannelDriverInterface;

abstract class AbstractDriver implements ChannelDriverInterface
{
    public function __construct(protected ChannelIntegration $integration) {}

    public function getAuthUrl(): ?string
    {
        return null;
    }

    public function handleOAuthCallback(array $params): void
    {
        // Override in OAuth-based drivers
    }

    public function pushProduct(array $productData): string
    {
        throw new \BadMethodCallException(static::class . ' does not support pushProduct.');
    }

    public function removeProduct(string $externalId): void
    {
        throw new \BadMethodCallException(static::class . ' does not support removeProduct.');
    }

    public function fetchProducts(int $page = 1, int $perPage = 100): array
    {
        throw new \BadMethodCallException(static::class . ' does not support fetchProducts.');
    }

    protected function credentials(): array
    {
        return $this->integration->credentials;
    }

    /**
     * Return the platform-level config value if set (SaaS mode),
     * otherwise fall back to the per-user stored credential (self-hosted mode).
     */
    protected function configOrCredential(string $configKey, string $credKey): ?string
    {
        $value = config($configKey);
        if ($value) return $value;
        return $this->credentials()[$credKey] ?? null;
    }

    /**
     * Write a signed HMAC nonce to oauth_state and return it.
     * Call this right before building the OAuth redirect URL.
     */
    protected function generateOAuthState(): string
    {
        $nonce = bin2hex(random_bytes(16));
        $hmac  = hash_hmac('sha256', $nonce . '|' . $this->integration->id, config('app.key'));
        $state = $nonce . '.' . $hmac;
        $this->integration->update(['oauth_state' => $state]);
        return $state;
    }

    /**
     * Verify the returned state matches what we stored.
     * Clears the column on success, throws on mismatch.
     */
    protected function verifyOAuthState(string $state): void
    {
        if (! $this->integration->oauth_state || $this->integration->oauth_state !== $state) {
            throw new \RuntimeException('OAuth state mismatch — possible CSRF attempt.');
        }
        $this->integration->update(['oauth_state' => null]);
    }
}
