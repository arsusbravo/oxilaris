<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\Product;
use App\Models\ChannelListing;
use App\Models\AdCampaign;
use App\Models\ChannelTypeSetting;
use App\Services\Channels\ChannelManager;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $disabled = ChannelTypeSetting::where('is_active', false)->pluck('channel_type')->toArray();

        $marketplaceTypes = ['bol' => 'BOL.com', 'amazon' => 'Amazon', 'tokopedia' => 'Tokopedia', 'shopee' => 'Shopee', 'olx' => 'OLX'];
        $adTypes          = ['google_ads' => 'Google Ads', 'facebook_ads' => 'Facebook Ads'];

        $activeMarketplaces = array_values(array_diff_key($marketplaceTypes, array_flip($disabled)));
        $activeAdChannels   = array_values(array_diff_key($adTypes, array_flip($disabled)));

        return view('dashboard', compact('activeMarketplaces', 'activeAdChannels'));
    }

    public function stats(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'stores'    => Store::where('user_id', $user->id)->count(),
            'products'  => Product::where('user_id', $user->id)->count(),
            'listings'  => ChannelListing::where('user_id', $user->id)->where('status', 'active')->count(),
            'campaigns' => AdCampaign::where('user_id', $user->id)->where('status', 'active')->count(),
        ]);
    }
}
