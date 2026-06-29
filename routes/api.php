<?php

use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — CRM Backend
|--------------------------------------------------------------------------
*/

// ── Public Auth ──────────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login',    [AuthController::class, 'login'])->name('login');
});

// ── Protected Routes ─────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::prefix('auth')->group(function () {
        Route::get('me',       [AuthController::class, 'me']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::post('logout',  [AuthController::class, 'logout']);
    });

    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index']);

    // Leads
    Route::apiResource('leads', LeadController::class);
    Route::prefix('leads/{lead}')->group(function () {
        Route::patch('assign',         [LeadController::class, 'assign']);
        Route::patch('status',         [LeadController::class, 'updateStatus']);
        Route::get('timeline',         [ActivityController::class, 'leadTimeline']);
        Route::post('convert',         [CustomerController::class, 'convertLead']);
    });

    // Customers
    Route::apiResource('customers', CustomerController::class)->except('store');
    Route::prefix('customers/{customer}')->group(function () {
        Route::get('timeline',         [ActivityController::class, 'customerTimeline']);
        Route::post('contacts',        [CustomerController::class, 'storeContact']);
        Route::delete('contacts/{contactId}', [CustomerController::class, 'destroyContact']);
        Route::post('notes',           [CustomerController::class, 'storeNote']);
    });

    // Tasks
    Route::apiResource('tasks', TaskController::class);
    Route::patch('tasks/{task}/complete', [TaskController::class, 'complete']);

    // Activities (global feed)
    Route::get('activities', [ActivityController::class, 'index']);

    // ── Admin-only routes ─────────────────────────────────────────────
    Route::middleware('role:Admin')->group(function () {
        Route::get('users', [\App\Http\Controllers\Api\UserController::class, 'index']);
        Route::post('users/{user}/toggle-active', [\App\Http\Controllers\Api\UserController::class, 'toggleActive']);
        Route::post('users/{user}/assign-role',   [\App\Http\Controllers\Api\UserController::class, 'assignRole']);
    });
});
