<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('user can login and load own profile via api', function () {
    $user = User::factory()->create([
        'login' => 'demo_user',
        'password' => Hash::make('secret-pass'),
    ]);

    $this->postJson('/api/auth/login', [
        'login' => 'demo_user',
        'password' => 'secret-pass',
        'remember' => true,
    ])
        ->assertOk()
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.login', 'demo_user');

    $this->getJson('/api/auth/user')
        ->assertOk()
        ->assertJsonPath('data.login', 'demo_user')
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'login',
                'email',
                'balance' => ['current', 'current_formatted'],
            ],
        ]);
});

test('login returns validation error for wrong credentials', function () {
    User::factory()->create([
        'login' => 'wrong_case',
        'password' => Hash::make('correct-pass'),
    ]);

    $this->postJson('/api/auth/login', [
        'login' => 'wrong_case',
        'password' => 'bad-pass',
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['login']);
});
