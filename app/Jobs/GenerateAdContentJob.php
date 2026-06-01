<?php

namespace App\Jobs;

use App\Models\AdCampaign;
use App\Models\Product;
use App\Models\JobLog;
use App\Services\AiContentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class GenerateAdContentJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;
    public int $timeout = 120;

    public function __construct(
        private AdCampaign $campaign,
        private array $productIds,
        private string $context = ''
    ) {}

    public function handle(AiContentService $aiService): void
    {
        $log = JobLog::create([
            'user_id'    => $this->campaign->user_id,
            'job_type'   => 'generate_ad_content',
            'status'     => 'running',
            'payload'    => ['campaign_id' => $this->campaign->id],
            'started_at' => now(),
        ]);

        $logger = Log::channel('jobs_ai');
        $logger->info('AI content generation started', ['campaign_id' => $this->campaign->id]);

        try {
            $campaign = $this->campaign->load(['channelIntegration', 'user']);
            $channelType = $campaign->channelIntegration->channel_type;
            $locale = $campaign->user->ai_locale ?? 'en';

            $products = empty($this->productIds)
                ? Product::where('user_id', $campaign->user_id)->limit(5)->get()
                : Product::whereIn('id', $this->productIds)->get();

            $generatedContent = [];

            foreach ($products as $product) {
                $copy = $aiService->generateAdCopy($product->toArray(), $channelType, $this->context, $locale);
                $generatedContent[] = [
                    'product_id'    => $product->id,
                    'product_title' => $product->title,
                    'ad_copy'       => $copy,
                ];
            }

            $campaign->update(['ai_content' => $generatedContent]);
            $log->update(['status' => 'done', 'result' => ['count' => count($generatedContent)], 'finished_at' => now()]);

            $logger->info('AI content generation completed', ['campaign_id' => $this->campaign->id, 'count' => count($generatedContent)]);
        } catch (Throwable $e) {
            $log->update(['status' => 'failed', 'error' => $e->getMessage(), 'finished_at' => now()]);

            $logger->error('AI content generation failed', ['campaign_id' => $this->campaign->id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
}
