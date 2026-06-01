<?php

namespace App\Services\Channels\Advertising;

use App\Services\Channels\AbstractDriver;
use Illuminate\Support\Facades\Http;

class TikTokAdsDriver extends AbstractDriver
{
    private const BASE_URL = 'https://business-api.tiktok.com/open_api/v1.3';

    private function headers(): array
    {
        return ['Access-Token' => $this->credentials()['access_token']];
    }

    private function advertiserId(): string
    {
        return $this->credentials()['advertiser_id'];
    }

    public function testConnection(): void
    {
        $response = Http::withHeaders($this->headers())
            ->get(self::BASE_URL . '/advertiser/info/', [
                'advertiser_ids' => json_encode([$this->advertiserId()]),
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException('TikTok Ads connection failed: ' . $response->status() . ' — ' . $response->body());
        }

        $code = $response->json('code', 0);
        if ($code !== 0) {
            throw new \RuntimeException('TikTok Ads error: ' . ($response->json('message') ?? $code));
        }
    }

    /**
     * Create a full campaign → ad group → ad for a product.
     * Returns the campaign ID.
     */
    public function createCampaign(string $name, string $adCopy, array $productData): string
    {
        // 1 — Campaign
        $campaignResp = Http::withHeaders($this->headers())
            ->post(self::BASE_URL . '/campaign/create/', [
                'advertiser_id'      => $this->advertiserId(),
                'campaign_name'      => mb_substr($name, 0, 255),
                'objective_type'     => 'TRAFFIC',
                'campaign_type'      => 'REGULAR_CAMPAIGN',
                'budget_mode'        => 'BUDGET_MODE_DAY',
                'budget'             => 20.00,
                'operation_status'   => 'DISABLE',
            ]);

        if (! $campaignResp->successful() || $campaignResp->json('code', 0) !== 0) {
            throw new \RuntimeException('TikTok Ads campaign creation failed: ' . $campaignResp->body());
        }

        $campaignId = (string) $campaignResp->json('data.campaign_id');

        // 2 — Ad Group
        $adGroupResp = Http::withHeaders($this->headers())
            ->post(self::BASE_URL . '/adgroup/create/', [
                'advertiser_id'    => $this->advertiserId(),
                'campaign_id'      => $campaignId,
                'adgroup_name'     => $name . ' — Group',
                'placement_type'   => 'PLACEMENT_TYPE_AUTOMATIC',
                'budget_mode'      => 'BUDGET_MODE_DAY',
                'budget'           => 10.00,
                'schedule_type'    => 'SCHEDULE_START_END',
                'schedule_start_time' => now()->addMinutes(5)->format('Y-m-d H:i:s'),
                'schedule_end_time'   => now()->addDays(30)->format('Y-m-d H:i:s'),
                'optimization_goal'   => 'CLICK',
                'bid_type'            => 'BID_TYPE_NO_BID',
                'operation_status'    => 'DISABLE',
            ]);

        if (! $adGroupResp->successful() || $adGroupResp->json('code', 0) !== 0) {
            throw new \RuntimeException('TikTok Ads group creation failed: ' . $adGroupResp->body());
        }

        $adGroupId = (string) $adGroupResp->json('data.adgroup_id');

        // 3 — Ad creative
        $adResp = Http::withHeaders($this->headers())
            ->post(self::BASE_URL . '/ad/create/', [
                'advertiser_id' => $this->advertiserId(),
                'adgroup_id'    => $adGroupId,
                'creatives'     => [[
                    'ad_name'          => mb_substr($name, 0, 255),
                    'ad_format'        => 'SINGLE_IMAGE',
                    'ad_text'          => mb_substr($adCopy, 0, 100),
                    'landing_page_url' => $productData['product_url'] ?? '',
                    'image_ids'        => [],
                    'call_to_action'   => 'SHOP_NOW',
                    'operation_status' => 'DISABLE',
                ]],
            ]);

        if (! $adResp->successful() || $adResp->json('code', 0) !== 0) {
            throw new \RuntimeException('TikTok Ads ad creation failed: ' . $adResp->body());
        }

        return $campaignId;
    }
}
