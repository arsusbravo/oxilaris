<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ChannelListing;
use App\Models\Product;
use App\Models\ChannelIntegration;
use App\Jobs\ExportProductToMarketplaceJob;
use Illuminate\Http\Request;

class ListingController extends Controller
{
    public function index(Request $request)
    {
        $listings = ChannelListing::where('user_id', $request->user()->id)
            ->with(['product', 'channelIntegration'])
            ->latest()
            ->paginate(50);

        return view('listings.index', compact('listings'));
    }

    public function apiIndex(Request $request)
    {
        $query = ChannelListing::where('user_id', $request->user()->id)
            ->with(['product', 'channelIntegration']);

        if ($request->filled('channel_integration_id')) {
            $query->where('channel_integration_id', $request->channel_integration_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->latest()->paginate(50));
    }

    public function bulkPush(Request $request)
    {
        $ids = $request->validate(['ids' => 'required|array', 'ids.*' => 'integer'])['ids'];

        ChannelListing::whereIn('id', $ids)
            ->where('user_id', $request->user()->id)
            ->get()
            ->each(function ($listing) {
                \App\Jobs\ExportProductToMarketplaceJob::dispatch($listing);
                $listing->update(['status' => 'pending']);
            });

        return response()->json(['success' => true]);
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->validate(['ids' => 'required|array', 'ids.*' => 'integer'])['ids'];

        ChannelListing::whereIn('id', $ids)
            ->where('user_id', $request->user()->id)
            ->delete();

        return response()->json(['success' => true]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'channel_integration_id' => 'required|exists:channel_integrations,id',
            'listing_data' => 'nullable|array',
        ]);

        $product = Product::where('id', $validated['product_id'])
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $channel = ChannelIntegration::where('id', $validated['channel_integration_id'])
            ->where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->firstOrFail();

        $listing = ChannelListing::firstOrCreate(
            ['product_id' => $product->id, 'channel_integration_id' => $channel->id],
            ['user_id' => $request->user()->id, 'status' => 'pending', 'listing_data' => $validated['listing_data'] ?? null]
        );

        if ($listing->wasRecentlyCreated) {
            ExportProductToMarketplaceJob::dispatch($listing);
        }

        return response()->json($listing->load(['product', 'channelIntegration']), 201);
    }

    public function push(Request $request, ChannelListing $listing)
    {
        abort_if($listing->user_id !== $request->user()->id, 403);

        ExportProductToMarketplaceJob::dispatch($listing);
        $listing->update(['status' => 'pending']);

        return response()->json(['success' => true, 'message' => 'Export queued.']);
    }

    public function destroy(Request $request, ChannelListing $listing)
    {
        abort_if($listing->user_id !== $request->user()->id, 403);
        $listing->delete();

        return response()->json(['deleted' => true]);
    }
}
