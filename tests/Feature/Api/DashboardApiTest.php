<?php

use App\Enums\OperationStatus;
use App\Enums\OperationType;
use App\Models\Operation;
use App\Models\User;
use App\Models\UserBalance;
use Laravel\Sanctum\Sanctum;

test('dashboard api returns current balance and five latest operations', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    UserBalance::query()->create([
        'user_id' => $user->id,
        'balance' => '1234.56',
    ]);

    foreach (range(1, 7) as $index) {
        Operation::query()->create([
            'user_id' => $user->id,
            'type' => OperationType::CREDIT,
            'status' => OperationStatus::COMPLETED,
            'amount' => '10.00',
            'description' => "Operation {$index}",
            'balance_before' => '0.00',
            'balance_after' => '10.00',
            'processed_at' => now()->addSeconds($index),
            'created_at' => now()->addSeconds($index),
            'updated_at' => now()->addSeconds($index),
        ]);
    }

    $this->getJson('/api/dashboard')
        ->assertOk()
        ->assertJsonPath('data.balance.current', '1234.56')
        ->assertJsonCount(5, 'data.recent_operations')
        ->assertJsonPath('data.recent_operations.0.description', 'Operation 7');
});
