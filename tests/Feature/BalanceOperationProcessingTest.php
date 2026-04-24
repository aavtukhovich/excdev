<?php

use App\Enums\OperationStatus;
use App\Enums\OperationType;
use App\Models\Operation;
use App\Models\User;
use App\Models\UserBalance;
use App\Services\BalanceOperationsService;

test('when two debit operations are queued and funds are enough for one only one is completed', function () {
    $user = User::factory()->create([
        'login' => 'queue_debit_user',
    ]);

    UserBalance::query()->create([
        'user_id' => $user->id,
        'balance' => '100.00',
    ]);

    $first = Operation::query()->create([
        'user_id' => $user->id,
        'type' => OperationType::DEBIT,
        'status' => OperationStatus::PENDING,
        'amount' => '70.00',
        'description' => 'Списание #1',
    ]);

    $second = Operation::query()->create([
        'user_id' => $user->id,
        'type' => OperationType::DEBIT,
        'status' => OperationStatus::PENDING,
        'amount' => '70.00',
        'description' => 'Списание #2',
    ]);

    $service = app(BalanceOperationsService::class);

    $service->process($first->id);
    $service->process($second->id);

    $statuses = Operation::query()
        ->whereIn('id', [$first->id, $second->id])
        ->pluck('status')
        ->map(fn ($status) => $status->value)
        ->all();

    expect($statuses)->toContain(OperationStatus::COMPLETED->value);
    expect($statuses)->toContain(OperationStatus::REJECTED->value);

    $balance = UserBalance::query()->where('user_id', $user->id)->value('balance');
    expect($balance)->toBe('30.00');
});

test('unexpected failure handling moves pending operation to rejected', function () {
    $user = User::factory()->create([
        'login' => 'queue_fail_user',
    ]);

    UserBalance::query()->create([
        'user_id' => $user->id,
        'balance' => '40.00',
    ]);

    $operation = Operation::query()->create([
        'user_id' => $user->id,
        'type' => OperationType::DEBIT,
        'status' => OperationStatus::PENDING,
        'amount' => '10.00',
        'description' => 'Списание с ошибкой',
    ]);

    $service = app(BalanceOperationsService::class);
    $service->rejectPendingAsFailed($operation->id, 'Тестовая ошибка job.');

    $operation->refresh();

    expect($operation->status)->toBe(OperationStatus::REJECTED);
    expect($operation->failure_reason)->toBe('Тестовая ошибка job.');
});
