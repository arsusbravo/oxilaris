<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ChannelIntegration;
use App\Models\ChannelTypeSetting;
use App\Services\Channels\ChannelManager;
use Illuminate\Http\Request;

class ChannelController extends Controller
{
    // OAuth-capable channel types that only need minimal user input
    private const OAUTH_TYPES = ['shopify', 'woocommerce', 'tiktok_shop', 'shopee'];

    public function __construct(private ChannelManager $channelManager) {}

    public function index(Request $request)
    {
        $channels = ChannelIntegration::where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return view('channels.index', compact('channels'));
    }

    public function apiIndex(Request $request)
    {
        return response()->json(
            ChannelIntegration::where('user_id', $request->user()->id)->latest()->get()
        );
    }

    public function create()
    {
        $disabled = ChannelTypeSetting::where('is_active', false)->pluck('channel_type')->toArray();

        $channelTypes = array_filter(
            ChannelManager::TYPES,
            fn($type) => ! in_array($type, $disabled),
            ARRAY_FILTER_USE_KEY
        );

        $platformAppSet = [
            'shopify'     => (bool) config('services.shopify.client_id'),
            'woocommerce' => (bool) config('services.woocommerce.app_name'),
            'tiktok_shop' => (bool) config('services.tiktok_shop.app_key'),
            'shopee'      => (bool) config('services.shopee.partner_id'),
        ];

        return view('channels.create', compact('channelTypes', 'platformAppSet'));
    }

    public function store(Request $request)
    {
        $type = $request->input('channel_type');

        $validated = $request->validate([
            'channel_type' => 'required|string|in:' . implode(',', array_keys(ChannelManager::TYPES)),
            'name'         => 'required|string|max:255',
            'credentials'  => in_array($type, self::OAUTH_TYPES) ? 'sometimes|array' : 'required|array',
        ]);

        $channel = ChannelIntegration::create([
            'user_id'      => $request->user()->id,
            'channel_type' => $validated['channel_type'],
            'name'         => $validated['name'],
            'credentials'  => $validated['credentials'] ?? [],
            'status'       => 'inactive',
        ]);

        // For OAuth channels redirect immediately to the authorization flow
        if (in_array($channel->channel_type, self::OAUTH_TYPES)) {
            return redirect()->route('channels.connect', $channel);
        }

        return redirect()->route('channels.show', $channel)
            ->with('success', 'Channel added. Test the connection to activate it.');
    }

    public function show(Request $request, ChannelIntegration $channel)
    {
        abort_if($channel->user_id !== $request->user()->id, 403);

        return view('channels.show', compact('channel'));
    }

    public function edit(Request $request, ChannelIntegration $channel)
    {
        abort_if($channel->user_id !== $request->user()->id, 403);

        $platformAppSet = [
            'shopify'     => (bool) config('services.shopify.client_id'),
            'woocommerce' => (bool) config('services.woocommerce.app_name'),
            'tiktok_shop' => (bool) config('services.tiktok_shop.app_key'),
            'shopee'      => (bool) config('services.shopee.partner_id'),
        ];

        return view('channels.edit', [
            'channel'        => $channel,
            'channelTypes'   => ChannelManager::TYPES,
            'platformAppSet' => $platformAppSet,
        ]);
    }

    public function update(Request $request, ChannelIntegration $channel)
    {
        abort_if($channel->user_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'credentials' => 'sometimes|array',
            'meta'        => 'sometimes|array',
        ]);

        if (isset($validated['credentials'])) {
            $channel->credentials = $validated['credentials'];
        }

        $channel->fill(['name' => $validated['name'], 'meta' => $validated['meta'] ?? $channel->meta]);
        $channel->save();

        return redirect()->route('channels.show', $channel)->with('success', 'Channel updated.');
    }

    public function destroy(Request $request, ChannelIntegration $channel)
    {
        abort_if($channel->user_id !== $request->user()->id, 403);
        $channel->delete();

        return redirect()->route('channels.index')->with('success', 'Channel removed.');
    }

    public function connect(Request $request, ChannelIntegration $channel)
    {
        abort_if($channel->user_id !== $request->user()->id, 403);

        $driver  = $this->channelManager->driver($channel);
        $authUrl = $driver->getAuthUrl();

        if ($authUrl) {
            return redirect($authUrl);
        }

        try {
            $driver->testConnection();
            $channel->update(['status' => 'active', 'last_used_at' => now()]);
            return back()->with('success', 'Connection successful.');
        } catch (\Exception $e) {
            $channel->update(['status' => 'error']);
            return back()->with('error', 'Connection failed: ' . $e->getMessage());
        }
    }

    public function callback(Request $request, ChannelIntegration $channel)
    {
        abort_if($channel->user_id !== $request->user()->id, 403);

        try {
            $params = array_merge($request->all(), ['state' => $request->query('state')]);
            $this->channelManager->driver($channel)->handleOAuthCallback($params);
            $channel->update(['status' => 'active', 'last_used_at' => now()]);
            return redirect()->route('channels.show', $channel)
                ->with('success', 'Channel connected successfully.');
        } catch (\Exception $e) {
            $channel->update(['status' => 'error']);
            return redirect()->route('channels.show', $channel)
                ->with('error', 'Connection failed: ' . $e->getMessage());
        }
    }

    /**
     * WooCommerce posts consumer_key + consumer_secret here server-to-server.
     * No session/auth cookie — validated via HMAC state instead.
     */
    public function woocommerceCallback(Request $request)
    {
        $integrationId = $request->input('user_id');
        $channel       = ChannelIntegration::findOrFail($integrationId);

        try {
            $params = array_merge($request->all(), ['state' => $request->query('state')]);
            $this->channelManager->driver($channel)->handleOAuthCallback($params);
            $channel->update(['status' => 'active', 'last_used_at' => now()]);
        } catch (\Exception $e) {
            $channel->update(['status' => 'error']);
        }

        return redirect()->route('channels.show', $channel);
    }

    /**
     * TikTok Shop redirects here with ?code=&state= — cannot use {channel} param
     * because TikTok requires a pre-registered static URI. Resolve via oauth_state.
     */
    public function tiktokShopCallback(Request $request)
    {
        $state   = $request->query('state', '');
        $channel = ChannelIntegration::where('oauth_state', $state)->firstOrFail();
        abort_if($channel->user_id !== $request->user()->id, 403);

        try {
            $this->channelManager->driver($channel)->handleOAuthCallback($request->all());
            $channel->update(['status' => 'active', 'last_used_at' => now()]);
            return redirect()->route('channels.show', $channel)
                ->with('success', 'TikTok Shop connected successfully.');
        } catch (\Exception $e) {
            $channel->update(['status' => 'error']);
            return redirect()->route('channels.show', $channel)
                ->with('error', 'Connection failed: ' . $e->getMessage());
        }
    }
}
