<?php

use App\Http\Controllers\AuthApiController;
use App\Http\Controllers\BillingWebhookController;
use App\Http\Controllers\TicketsApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Login API
Route::group(['middleware' => ['web']], function () {
    Route::post('/login', [AuthApiController::class, 'login'])->name('loginApi');
    Route::get('/logout', [AuthApiController::class, 'logout'])->name('logoutApi');
});

Route::middleware(['auth:sanctum', 'checkAccessToken', 'check.tenant', 'check.subscription'])->group(function () {

    // Tickets API
    Route::prefix('tickets')->name('tickets.')->group(function () {
        Route::post('/list', [TicketsApiController::class, 'index'])->name('list');
        Route::post('/create', [TicketsApiController::class, 'store'])->name('create');
        Route::get('/detail/{id}', [TicketsApiController::class, 'detail'])->name('detail');
        Route::post('/update', [TicketsApiController::class, 'update'])->name('update');
        Route::post('/action/update', [TicketsApiController::class, 'action'])->name('action.update');

        Route::patch('/assign', [TicketsApiController::class, 'assign'])->name('assign');
    });
});

Route::post('/webhook/billing', [BillingWebhookController::class, 'handle']);