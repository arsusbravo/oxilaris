<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChannelTypeSetting;
use App\Services\Channels\ChannelManager;

class ChannelSettingsController extends Controller
{
    public function index()
    {
        // Ensure every known channel type has a DB record
        foreach (array_keys(ChannelManager::TYPES) as $type) {
            ChannelTypeSetting::firstOrCreate(
                ['channel_type' => $type],
                ['is_active'    => true]
            );
        }

        $settings = ChannelTypeSetting::all()->keyBy('channel_type');

        $groups = [
            'Stores'       => ['woocommerce', 'shopify', 'magento', 'cs_cart'],
            'Marketplaces' => ['bol', 'amazon', 'tokopedia', 'shopee', 'olx'],
            'Advertising'  => ['google_ads', 'facebook_ads'],
        ];

        return view('admin.channel-settings.index', compact('settings', 'groups'));
    }

    public function toggle(string $type)
    {
        $setting = ChannelTypeSetting::firstOrCreate(
            ['channel_type' => $type],
            ['is_active'    => true]
        );

        $setting->update(['is_active' => ! $setting->is_active]);

        $label  = ChannelManager::TYPES[$type] ?? $type;
        $status = $setting->is_active ? 'enabled' : 'disabled';

        return back()->with('success', "{$label} has been {$status}.");
    }
}
