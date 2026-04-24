<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserBalance;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class UserService
{
    /**
     * @throws ValidationException
     */
    public function create(string $name, string $login, string $password, ?string $email = null): User
    {
        $payload = [
            'name' => trim($name),
            'login' => Str::lower(trim($login)),
            'password' => $password,
            'email' => $email ? Str::lower(trim($email)) : null,
        ];

        Validator::make($payload, [
            'name' => ['required', 'string', 'max:255'],
            'login' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Za-z0-9._-]+$/',
                Rule::unique(User::class, 'login'),
            ],
            'email' => ['nullable', 'email', 'max:255', Rule::unique(User::class, 'email')],
            'password' => ['required', 'string', 'min:8'],
        ])->validate();

        $user = User::query()->create([
            'name' => $payload['name'],
            'login' => $payload['login'],
            'email' => $payload['email'],
            'password' => Hash::make($payload['password']),
        ]);

        UserBalance::query()->create([
            'user_id' => $user->id,
            'balance' => '0.00',
        ]);

        return $user->load('balance');
    }
}
