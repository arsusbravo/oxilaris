<?php

namespace App\Jobs;

use App\Models\ChannelListing;
use App\Models\JobLog;
use App\Services\Channels\ChannelManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class ExportProductToMarketplaceJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(private ChannelListing $listing) {}

    public function handle(ChannelManager $channelManager): void
    {
        $listing = $this->listing->load(['product', 'channelIntegration']);

        $log = JobLog::create([
            'user_id'    => $listing->user_id,
            'job_type'   => 'export_product',
            'status'     => 'running',
            'payload'    => ['listing_id' => $listing->id, 'product_id' => $listing->product_id],
            'started_at' => now(),
        ]);

        $logger = Log::channel('jobs_export');
        $logger->info('Export started', [
            'listing_id' => $listing->id,
            'product'    => $listing->product->title,
            'channel'    => $listing->channelIntegration->name,
        ]);

        try {
            $driver = $channelManager->driver($listing->channelIntegration);

            $productData = array_merge(
                $listing->product->toArray(),
                $listing->listing_data ?? []
            );

            $externalId = $driver->pushProduct($productData);

            $listing->update([
                'status'              => 'active',
                'external_listing_id' => $externalId,
                'last_pushed_at'      => now(),
                'error_message'       => null,
            ]);

            $log->update(['status' => 'done', 'result' => ['external_id' => $externalId], 'finished_at' => now()]);
            $logger->info('Export completed', ['listing_id' => $listing->id, 'external_id' => $externalId]);
        } catch (Throwable $e) {
            $listing->update(['status' => 'error', 'error_message' => $e->getMessage()]);
            $log->update(['status' => 'failed', 'error' => $e->getMessage(), 'finished_at' => now()]);

            $logger->error('Export failed', ['listing_id' => $listing->id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
}
