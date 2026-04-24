<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserBalance;
use Illuminate\Auth\AuthManager;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuthService
{
    public function __construct(private readonly AuthManager $auth)
    {
    }

    /**
     * @throws AuthenticationException
     */
    public function login(Request $request, string $login, string $password, bool $remember = false): User
    {
        $credentials = [
            'login' => Str::lower(trim($login)),
            'password' => $password,
        ];

        if (! $this->auth->guard('web')->attempt($credentials, $remember)) {
            throw new AuthenticationException('Неверный логин или пароль.');
        }

        $request->session()->regenerate();

        return $this->loadUser($request->user());
    }

    public function logout(Request $request): void
    {
        $this->auth->guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    public function loadUser(User $user): User
    {
        UserBalance::query()->firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => '0.00'],
        );

        return $user->fresh('balance');
    }
}
