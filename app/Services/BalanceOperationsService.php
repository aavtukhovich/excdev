<?php

namespace App\Services;

use App\Enums\OperationStatus;
use App\Enums\OperationType;
use App\Jobs\ProcessBalanceOperationJob;
use App\Models\Operation;
use App\Models\User;
use App\Models\UserBalance;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class BalanceOperationsService
{
    /**
     * @throws ModelNotFoundException
     */
    public function queueByLogin(string $login, OperationType $type, string $amount, string $description): Operation
    {
        $normalizedLogin = Str::lower(trim($login));
        $description = trim($description);
        $normalizedAmount = str_replace(',', '.', trim($amount));

        if (! preg_match('/^\d+(?:\.\d{1,2})?$/', $normalizedAmount)) {
            throw new InvalidArgumentException('Некорректный формат суммы.');
        }

        $amountMinor = (int) round(((float) $normalizedAmount) * 100);

        if ($amountMinor <= 0) {
            throw new InvalidArgumentException('Сумма должна быть больше нуля.');
        }

        if ($description === '') {
            throw new InvalidArgumentException('Описание операции обязательно.');
        }

        $user = User::query()->where('login', $normalizedLogin)->firstOrFail();

        UserBalance::query()->firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => '0.00'],
        );

        $operation = Operation::query()->create([
            'user_id' => $user->id,
            'type' => $type,
            'status' => OperationStatus::PENDING,
            'amount' => round($amountMinor / 100, 2),
            'description' => $description,
        ]);

        ProcessBalanceOperationJob::dispatch($operation->id);

        return $operation->refresh();
    }

    public function process(int $operationId): Operation
    {
        return DB::transaction(function () use ($operationId) {
            /** @var Operation $operation */
            $operation = Operation::query()
                ->whereKey($operationId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($operation->status !== OperationStatus::PENDING) {
                return $operation;
            }

            $timestamp = now();

            DB::table('user_balances')->insertOrIgnore([
                'user_id' => $operation->user_id,
                'balance' => '0.00',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

            /** @var UserBalance $balance */
            $balance = UserBalance::query()
                ->where('user_id', $operation->user_id)
                ->lockForUpdate()
                ->firstOrFail();

            $currentMinor = (int) round(((float) $balance->balance) * 100);
            $amountMinor = (int) round(((float) $operation->amount) * 100);

            if ($operation->type === OperationType::DEBIT && $currentMinor < $amountMinor) {
                $operation->forceFill([
                    'status' => OperationStatus::REJECTED,
                    'balance_before' => round($currentMinor / 100, 2),
                    'balance_after' => round($currentMinor / 100, 2),
                    'failure_reason' => 'Недостаточно средств.',
                    'processed_at' => $timestamp,
                ])->save();

                return $operation->refresh();
            }

            $nextMinor = $currentMinor + ($amountMinor * $operation->type->multiplier());

            $balance->forceFill([
                'balance' => round($nextMinor / 100, 2),
            ])->save();

            $operation->forceFill([
                'status' => OperationStatus::COMPLETED,
                'balance_before' => round($currentMinor / 100, 2),
                'balance_after' => round($nextMinor / 100, 2),
                'failure_reason' => null,
                'processed_at' => $timestamp,
            ])->save();

            return $operation->refresh();
        });
    }

    public function rejectPendingAsFailed(int $operationId, string $reason = 'Ошибка обработки операции.'): ?Operation
    {
        return DB::transaction(function () use ($operationId, $reason) {
            /** @var Operation|null $operation */
            $operation = Operation::query()
                ->whereKey($operationId)
                ->lockForUpdate()
                ->first();

            if ($operation === null || $operation->status !== OperationStatus::PENDING) {
                return $operation;
            }

            /** @var UserBalance|null $balance */
            $balance = UserBalance::query()
                ->where('user_id', $operation->user_id)
                ->lockForUpdate()
                ->first();

            $currentMinor = $balance === null ? 0 : (int) round(((float) $balance->balance) * 100);

            $operation->forceFill([
                'status' => OperationStatus::REJECTED,
                'balance_before' => round($currentMinor / 100, 2),
                'balance_after' => round($currentMinor / 100, 2),
                'failure_reason' => $reason,
                'processed_at' => now(),
            ])->save();

            return $operation->refresh();
        });
    }
}
