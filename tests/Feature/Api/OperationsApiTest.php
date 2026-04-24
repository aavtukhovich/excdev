<?php

use App\Enums\OperationStatus;
use App\Enums\OperationType;
use App\Models\Operation;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('operations api filters by description and sorts by date', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    Operation::query()->create([
        'user_id' => $user->id,
        'type' => OperationType::CREDIT,
        'status' => OperationStatus::COMPLETED,
        'amount' => '10.00',
        'description' => 'Monthly bonus',
        'created_at' => now()->subDays(2),
        'updated_at' => now()->subDays(2),
    ]);

    Operation::query()->create([
        'user_id' => $user->id,
        'type' => OperationType::DEBIT,
        'status' => OperationStatus::COMPLETED,
        'amount' => '3.00',
        'description' => 'Food',
        'created_at' => now()->subDay(),
        'updated_at' => now()->subDay(),
    ]);

    Operation::query()->create([
        'user_id' => $user->id,
        'type' => OperationType::CREDIT,
        'status' => OperationStatus::COMPLETED,
        'amount' => '15.00',
        'description' => 'Quarterly bonus',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->getJson('/api/operations?search=bonus&sort=asc')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.description', 'Monthly bonus')
        ->assertJsonPath('data.1.description', 'Quarterly bonus');
});
