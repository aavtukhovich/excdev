<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AuthenticatedUserRequest;
use App\Http\Resources\AuthenticatedUserResource;
use App\Services\AuthService;

class CurrentUserController extends Controller
{
    public function __invoke(AuthenticatedUserRequest $request, AuthService $service): AuthenticatedUserResource
    {
        return new AuthenticatedUserResource(
            $service->loadUser($request->user()),
        );
    }
}
