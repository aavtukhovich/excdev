<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LogoutRequest;
use App\Http\Resources\StatusResource;
use App\Services\AuthService;

class LogoutController extends Controller
{
    public function __invoke(LogoutRequest $request, AuthService $service): StatusResource
    {
        $service->logout($request);

        return new StatusResource([
            'status' => 'ok',
            'message' => 'Сессия завершена.',
        ]);
    }
}
