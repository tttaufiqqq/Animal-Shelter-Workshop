<?php

namespace App\Jobs;

use App\Models\AuditLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreateAuditLog implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $timeout = 30;

    public $backoff = [5, 10, 20]; // Exponential backoff

    protected $data;

    /**
     * Create a new job instance.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
        $this->onQueue('audit'); // Dedicated queue
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            AuditLog::create($this->data);
        } catch (\Exception $e) {
            Log::error('Failed to create audit log', [
                'data' => $this->data,
                'error' => $e->getMessage(),
            ]);

            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception)
    {
        // Called when all retries exhausted
        Log::critical('Audit log creation failed permanently', [
            'data' => $this->data,
            'error' => $exception->getMessage(),
        ]);
    }
}
