<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BalanceController;
use App\Http\Controllers\Api\CalendarController;
use App\Http\Controllers\Api\CheckInController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\IncomeController;
use App\Http\Controllers\Api\ObligationController;
use App\Http\Controllers\Api\ObligationPaymentController;
use App\Http\Controllers\Api\Reconciliation\BankImportController;
use App\Http\Controllers\Api\Reconciliation\ReconciliationSettingsController;
use App\Http\Controllers\Api\SavingController;
use App\Http\Controllers\Api\SettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'app' => config('app.name'),
        'time' => now()->toIso8601String(),
    ]);
});

Route::middleware('throttle:auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    Route::get('/dashboard', DashboardController::class);
    Route::get('/calendar', CalendarController::class);
    Route::post('/balance', [BalanceController::class, 'store']);
    Route::post('/check-in', [CheckInController::class, 'store']);

    Route::get('/settings', [SettingsController::class, 'show']);
    Route::put('/settings', [SettingsController::class, 'update']);

    Route::prefix('reconciliation')->group(function () {
        Route::get('/settings', [ReconciliationSettingsController::class, 'show']);
        Route::put('/settings', [ReconciliationSettingsController::class, 'update']);

        Route::get('/imports', [BankImportController::class, 'index']);
        Route::post('/imports', [BankImportController::class, 'store']);
        Route::get('/imports/{id}', [BankImportController::class, 'show']);
        Route::get('/imports/{id}/download', [BankImportController::class, 'download']);
    });

    Route::apiResource('obligations', ObligationController::class);
    Route::prefix('obligations/{obligation}')->group(function () {
        Route::get('payments', [ObligationPaymentController::class, 'index']);
        Route::post('payments', [ObligationPaymentController::class, 'store']);
        Route::delete('payments/{payment}', [ObligationPaymentController::class, 'destroy']);
        Route::post('close', [ObligationPaymentController::class, 'close']);
        Route::post('reopen', [ObligationPaymentController::class, 'reopen']);
    });

    Route::apiResource('incomes', IncomeController::class);
    Route::apiResource('savings', SavingController::class);
});
