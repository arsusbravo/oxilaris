<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ChannelIntegration;
use App\Models\ChannelTypeSetting;
use App\Services\Channels\ChannelManager;
use Illuminate\Http\Request;

class ChannelController extends Controller
{
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

        return view('channels.create', compact('channelTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'channel_type' => 'required|string|in:' . implode(',', array_keys(ChannelManager::TYPES)),
            'name' => 'required|string|max:255',
            'credentials' => 'required|array',
        ]);

        $channel = ChannelIntegration::create([
            'user_id' => $request->user()->id,
            'channel_type' => $validated['channel_type'],
            'name' => $validated['name'],
            'credentials' => $validated['credentials'],
            'status' => 'inactive',
        ]);

        return redirect()->route('channels.show', $channel)->with('success', 'Channel added. Test the connection to activate it.');
    }

    public function show(Request $request, ChannelIntegration $channel)
    {
        abort_if($channel->user_id !== $request->user()->id, 403);

        return view('channels.show', compact('channel'));
    }

    public function edit(Request $request, ChannelIntegration $channel)
    {
        abort_if($channel->user_id !== $request->user()->id, 403);

        return view('channels.edit', [
            'channel' => $channel,
            'channelTypes' => ChannelManager::TYPES,
        ]);
    }

    public function update(Request $request, ChannelIntegration $channel)
    {
        abort_if($channel->user_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'credentials' => 'sometimes|array',
            'meta' => 'sometimes|array',
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

        $driver = $this->channelManager->driver($channel);
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

        $driver = $this->channelManager->driver($channel);
        $driver->handleOAuthCallback($request->all());
        $channel->update(['status' => 'active', 'last_used_at' => now()]);

        return redirect()->route('channels.show', $channel)->with('success', 'Channel connected successfully.');
    }
}
