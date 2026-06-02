<?php

namespace App\Services\Channels\FoodDelivery;

use App\Services\Channels\AbstractDriver;

/**
 * GoFood (Gojek) channel driver.
 *
 * Requires a formal merchant partnership with Gojek to obtain API credentials.
 * Apply at: https://www.gojek.com/en-id/partner/
 *
 * Required .env keys:
 *   GOFOOD_MERCHANT_ID, GOFOOD_API_KEY, GOFOOD_API_SECRET
 *
 * Until credentials are obtained, this driver is scaffolded and ready —
 * replace the RuntimeException stubs with real API calls once access is granted.
 */
class GoFoodDriver extends AbstractDriver
{
    // API_BASE = 'https://api.gofood.co.id/v1' — define once credentials are confirmed

    public function testConnection(): void
    {
        $this->requireCredentials();

        // TODO: Replace with real health-check endpoint once API access is granted
        throw new \RuntimeException(
            'GoFood API credentials not yet configured. ' .
            'Apply for merchant API access at https://www.gojek.com/en-id/partner/'
        );
    }

    public function pushProduct(array $productData): string
    {
        $this->requireCredentials();

        // TODO: Map $productData to GoFood menu item payload and POST to API
        // Expected payload structure (to be confirmed with Gojek API docs):
        // {
        //   "name": $productData['title'],
        //   "description": $productData['description'],
        //   "price": (int) ($productData['price'] * 100), // in cents
        //   "photos": $productData['images'],
        //   "category": $productData['categories'][0] ?? null,
        //   "is_available": $productData['stock'] > 0,
        //   "modifiers": $productData['attributes'],  // add-ons
        // }

        throw new \RuntimeException('GoFood pushProduct not yet implemented — API access required.');
    }

    public function fetchProducts(int $page = 1, int $perPage = 100): array
    {
        $this->requireCredentials();

        // TODO: GET /menu/items with pagination
        throw new \RuntimeException('GoFood fetchProducts not yet implemented — API access required.');
    }

    public function removeProduct(string $externalId): void
    {
        $this->requireCredentials();

        // TODO: PATCH /menu/items/{externalId} with is_available: false
        throw new \RuntimeException('GoFood removeProduct not yet implemented — API access required.');
    }

    private function requireCredentials(): void
    {
        $creds = $this->credentials();

        if (empty($creds['merchant_id']) || empty($creds['api_key'])) {
            throw new \RuntimeException(
                'GoFood credentials missing. Enter your Merchant ID and API Key when adding the channel.'
            );
        }
    }
}
