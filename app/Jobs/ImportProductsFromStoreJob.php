<?php

namespace App\Jobs;

use App\Models\Store;
use App\Models\JobLog;
use App\Services\ProductImportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Throwable;

class ImportProductsFromStoreJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $timeout = 300;

    public function __construct(private Store $store) {}

    public function middleware(): array
    {
        return [new WithoutOverlapping('import-store-' . $this->store->id)];
    }

    public function handle(ProductImportService $importService): void
    {
        $log = JobLog::create([
            'user_id'    => $this->store->user_id,
            'job_type'   => 'import_products',
            'status'     => 'running',
            'payload'    => ['store_id' => $this->store->id],
            'started_at' => now(),
        ]);

        try {
            $result = $importService->importFromStore($this->store);

            $this->store->update(['sync_status' => 'idle', 'last_synced_at' => now()]);

            $log->update(['status' => 'done', 'result' => $result, 'finished_at' => now()]);
        } catch (Throwable $e) {
            $this->store->update(['sync_status' => 'error']);
            $log->update(['status' => 'failed', 'error' => mb_substr($e->getMessage(), 0, 2000), 'finished_at' => now()]);
            throw $e;
        }
    }
}
