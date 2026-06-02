<?php

namespace App\Services\Channels\FoodDelivery;

use App\Services\Channels\AbstractDriver;

/**
 * GrabFood channel driver.
 *
 * Requires enrollment in the Grab Merchant Open Platform.
 * Apply at: https://developer.grab.com/
 *
 * Required .env keys:
 *   GRABFOOD_CLIENT_ID, GRABFOOD_CLIENT_SECRET
 *
 * GrabFood uses OAuth 2.0 (client credentials flow) for API access.
 * Until credentials are obtained, this driver is scaffolded and ready —
 * replace the RuntimeException stubs with real API calls once access is granted.
 */
class GrabFoodDriver extends AbstractDriver
{
    // API_BASE  = 'https://partner-api.grab.com/grabfood/v1'
    // TOKEN_URL = 'https://partner-api.grab.com/grabid/v1/oauth2/token'

    public function testConnection(): void
    {
        $this->requireCredentials();

        // TODO: POST to TOKEN_URL with client credentials, then GET /merchant/profile
        throw new \RuntimeException(
            'GrabFood API credentials not yet configured. ' .
            'Apply for merchant API access at https://developer.grab.com/'
        );
    }

    public function getAuthUrl(): ?string
    {
        // GrabFood uses OAuth 2.0 client credentials (server-to-server), not user-facing OAuth.
        // No redirect URL needed — return null.
        return null;
    }

    public function pushProduct(array $productData): string
    {
        $this->requireCredentials();

        // TODO: Map $productData to GrabFood menu item payload and POST to API
        // GrabFood Merchant Open Platform expected payload (confirm with docs):
        // {
        //   "merchantID": $merchantId,
        //   "items": [{
        //     "name": $productData['title'],
        //     "description": $productData['description'],
        //     "price": { "amount": (int)($productData['price'] * 100), "currency": "IDR" },
        //     "photos": [{ "url": $productData['images'][0] }],
        //     "availableStatus": $productData['stock'] > 0 ? "AVAILABLE" : "UNAVAILABLE",
        //     "modifierGroups": $productData['attributes'],
        //   }]
        // }

        throw new \RuntimeException('GrabFood pushProduct not yet implemented — API access required.');
    }

    public function fetchProducts(int $page = 1, int $perPage = 100): array
    {
        $this->requireCredentials();

        // TODO: GET /merchant/{merchantId}/menu
        throw new \RuntimeException('GrabFood fetchProducts not yet implemented — API access required.');
    }

    public function removeProduct(string $externalId): void
    {
        $this->requireCredentials();

        // TODO: POST /item/availability with { "itemID": $externalId, "availableStatus": "UNAVAILABLE" }
        throw new \RuntimeException('GrabFood removeProduct not yet implemented — API access required.');
    }

    private function requireCredentials(): void
    {
        $creds = $this->credentials();

        if (empty($creds['client_id']) || empty($creds['client_secret'])) {
            throw new \RuntimeException(
                'GrabFood credentials missing. Enter your Client ID and Client Secret when adding the channel.'
            );
        }
    }
}
