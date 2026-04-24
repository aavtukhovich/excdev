<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Resources\AuthenticatedUserResource;
use App\Services\AuthService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * @throws ValidationException
     */
    public function __invoke(LoginRequest $request, AuthService $service): AuthenticatedUserResource
    {
        try {
            $user = $service->login(
                request: $request,
                login: $request->validated('login'),
                password: $request->validated('password'),
            );
        } catch (AuthenticationException) {
            throw ValidationException::withMessages([
                'login' => 'Неверный логин или пароль.',
            ]);
        }

        return new AuthenticatedUserResource($user);
    }
}
