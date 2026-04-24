<?php

use App\Models\User;
use App\Models\UserBalance;
use App\Models\Operation;
use Illuminate\Support\Facades\Hash;

test('user:create command creates user and zero balance', function () {
    $this->artisan('user:create', [
        'login' => 'cli_user',
        'password' => 'cli-pass-123',
        '--name' => 'CLI User',
        '--email' => 'cli@example.com',
    ])->assertExitCode(0);

    $user = User::query()->where('login', 'cli_user')->firstOrFail();

    expect(Hash::check('cli-pass-123', $user->password))->toBeTrue();
    expect(UserBalance::query()->where('user_id', $user->id)->exists())->toBeTrue();
});

test('balance:operate command accepts russian кредит and дебит values', function () {
    $this->artisan('user:create', [
        'login' => 'ops_cli_user',
        'password' => 'ops-pass-123',
    ])->assertExitCode(0);

    $this->artisan('balance:operate', [
        'login' => 'ops_cli_user',
        'type' => 'кредит',
        'amount' => '150.00',
        'description' => 'Начисление',
    ])->assertExitCode(0);

    $this->artisan('balance:operate', [
        'login' => 'ops_cli_user',
        'type' => 'дебит',
        'amount' => '30.00',
        'description' => 'Списание',
    ])->assertExitCode(0);

    $user = User::query()->where('login', 'ops_cli_user')->firstOrFail();

    expect(UserBalance::query()->where('user_id', $user->id)->value('balance'))->toBe('120.00');
    expect(Operation::query()->where('user_id', $user->id)->count())->toBe(2);
});
