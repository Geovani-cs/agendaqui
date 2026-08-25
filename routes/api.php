<?php

use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CashClosingController;
use App\Http\Controllers\Api\CollaboratorController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VehicleTypeController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', 'identify.tenant'])->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    /* ---- Operação e consulta: admin + colaborador ---- */

    // Agendamentos (o index com filtros também é a "Consultar serviços"/histórico)
    Route::get('/appointments', [AppointmentController::class, 'index']);
    Route::get('/clients', [AppointmentController::class, 'clients']);
    Route::get('/appointments/{appointment}', [AppointmentController::class, 'show']);
    Route::post('/appointments', [AppointmentController::class, 'store']);
    Route::put('/appointments/{appointment}', [AppointmentController::class, 'update']);
    Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy']);
    Route::post('/appointments/{appointment}/start', [AppointmentController::class, 'start']);
    Route::post('/appointments/{appointment}/complete', [AppointmentController::class, 'complete']);
    Route::post('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel']);
    Route::post('/appointments/{appointment}/reopen', [AppointmentController::class, 'reopen']);

    // Despesas
    Route::get('/expenses', [ExpenseController::class, 'index']);
    Route::post('/expenses', [ExpenseController::class, 'store']);
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy']);

    // Leituras necessárias para montar formulários (agendar, consultar)
    Route::get('/vehicle-types', [VehicleTypeController::class, 'index']);
    Route::get('/services', [ServiceController::class, 'index']);
    Route::get('/collaborators', [CollaboratorController::class, 'index']);
    Route::get('/settings', [SettingController::class, 'show']);

    /* ---- Somente ADMIN ---- */
    Route::middleware('role:admin')->group(function () {
        Route::apiResource('vehicle-types', VehicleTypeController::class)->except('index');
        Route::apiResource('services', ServiceController::class)->except('index');
        Route::apiResource('collaborators', CollaboratorController::class)->except('index');
        Route::apiResource('users', UserController::class);

        // Relatórios
        Route::get('/reports/services', [ReportController::class, 'services']);
        Route::get('/reports/financial', [ReportController::class, 'financial']);

        // Pagamentos de colaboradores (extrato / avulsos)
        Route::get('/payments/ledger', [PaymentController::class, 'ledger']);
        Route::get('/payments', [PaymentController::class, 'index']);
        Route::post('/payments', [PaymentController::class, 'store']);
        Route::delete('/payments/{payment}', [PaymentController::class, 'destroy']);

        // Fechar caixa do dia
        Route::get('/cash/{date}', [CashClosingController::class, 'show']);
        Route::post('/cash/{date}/close', [CashClosingController::class, 'close']);
        Route::post('/cash/{date}/toggle-paid', [CashClosingController::class, 'togglePaid']);

        // Configurações
        Route::put('/settings', [SettingController::class, 'update']);
    });
});


// ===== Painel do dono (landlord) — sem identify.tenant: enxerga todos =====
Route::middleware(['auth:sanctum', 'super-admin'])->prefix('landlord')->group(function () {
    Route::get('/tenants', [\App\Http\Controllers\Api\LandlordController::class, 'tenants']);
    Route::post('/tenants', [\App\Http\Controllers\Api\LandlordController::class, 'store']);
    Route::patch('/tenants/{tenant}/status', [\App\Http\Controllers\Api\LandlordController::class, 'updateStatus']);
});
