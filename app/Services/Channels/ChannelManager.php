<?php

namespace App\Services\Channels;

use App\Models\ChannelIntegration;
use App\Services\Channels\Contracts\ChannelDriverInterface;
use App\Services\Channels\Stores\WooCommerceDriver;
use App\Services\Channels\Stores\ShopifyDriver;
use App\Services\Channels\Stores\MagentoDriver;
use App\Services\Channels\Stores\CsCartDriver;
use App\Services\Channels\Marketplaces\BolDriver;
use App\Services\Channels\Marketplaces\AmazonDriver;
use App\Services\Channels\Marketplaces\TikTokShopDriver;
use App\Services\Channels\Marketplaces\ShopeeDriver;
use App\Services\Channels\Marketplaces\OlxDriver;
use App\Services\Channels\Advertising\GoogleAdsDriver;
use App\Services\Channels\Advertising\FacebookAdsDriver;
use App\Services\Channels\Advertising\TikTokAdsDriver;
use InvalidArgumentException;

class ChannelManager
{
    /** Human-readable labels keyed by channel_type slug */
    public const TYPES = [
        'woocommerce'  => 'WooCommerce',
        'shopify'      => 'Shopify',
        'magento'      => 'Magento 2',
        'cs_cart'      => 'CS-Cart',
        'bol'          => 'BOL.com',
        'amazon'       => 'Amazon',
        'tiktok_shop'  => 'TikTok Shop',
        'shopee'       => 'Shopee',
        'olx'          => 'OLX',
        'google_ads'   => 'Google Ads',
        'facebook_ads' => 'Facebook Ads',
        'tiktok_ads'   => 'TikTok Ads',
    ];

    private static array $drivers = [
        'woocommerce'  => WooCommerceDriver::class,
        'shopify'      => ShopifyDriver::class,
        'magento'      => MagentoDriver::class,
        'cs_cart'      => CsCartDriver::class,
        'bol'          => BolDriver::class,
        'amazon'       => AmazonDriver::class,
        'tiktok_shop'  => TikTokShopDriver::class,
        'shopee'       => ShopeeDriver::class,
        'olx'          => OlxDriver::class,
        'google_ads'   => GoogleAdsDriver::class,
        'facebook_ads' => FacebookAdsDriver::class,
        'tiktok_ads'   => TikTokAdsDriver::class,
    ];

    public function driver(ChannelIntegration $integration): ChannelDriverInterface
    {
        $class = self::$drivers[$integration->channel_type] ?? null;

        if (! $class) {
            throw new InvalidArgumentException("No driver for channel type: {$integration->channel_type}");
        }

        return new $class($integration);
    }
}
