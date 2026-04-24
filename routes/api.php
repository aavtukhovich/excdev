<?php

use App\Http\Controllers\Api\CurrentUserController;
use App\Http\Controllers\Api\DashboardSummaryController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\LogoutController;
use App\Http\Controllers\Api\OperationHistoryController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->middleware('web')->group(function (): void {
    Route::post('/login', LoginController::class)->name('api.auth.login');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/user', CurrentUserController::class)->name('api.auth.user');
        Route::post('/logout', LogoutController::class)->name('api.auth.logout');
    });
});

Route::middleware(['web', 'auth:sanctum'])->group(function (): void {
    Route::get('/dashboard', DashboardSummaryController::class)->name('api.dashboard.show');
    Route::get('/operations', OperationHistoryController::class)->name('api.operations.index');
});
