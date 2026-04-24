<?php

namespace App\Jobs;

use App\Services\BalanceOperationsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;
use Throwable;

class ProcessBalanceOperationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public readonly int $operationId)
    {
    }

    public function handle(BalanceOperationsService $service): void
    {
        $service->process($this->operationId);
    }

    public function failed(Throwable $exception): void
    {
        $message = trim($exception->getMessage());
        $reason = $message === ''
            ? 'Ошибка обработки операции.'
            : Str::limit("Ошибка обработки операции: {$message}", 240);

        app(BalanceOperationsService::class)->rejectPendingAsFailed($this->operationId, $reason);
    }
}
