<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\PortalController;
use App\Http\Controllers\Api\PublicController;
use App\Http\Controllers\Api\QuoteController;
use Illuminate\Support\Facades\Route;

Route::prefix('public')->group(function () {
    Route::get('/home', [PublicController::class, 'home']);
    Route::get('/services', [PublicController::class, 'services']);
    Route::get('/blog', [BlogController::class, 'index']);
    Route::get('/blog/{slug}', [BlogController::class, 'show']);
    Route::get('/quote', [QuoteController::class, 'create']);
    Route::post('/quote', [QuoteController::class, 'store']);
    Route::get('/booking/services', [BookingController::class, 'services']);
    Route::post('/bookings', [BookingController::class, 'store']);
});

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
    });
});

Route::middleware(['auth:sanctum', 'customer'])->prefix('portal')->group(function () {
    Route::get('/dashboard', [PortalController::class, 'dashboard']);
    Route::get('/vehicles', [PortalController::class, 'vehicles']);
    Route::post('/vehicles', [PortalController::class, 'storeVehicle']);
    Route::get('/bookings', [PortalController::class, 'bookings']);
    Route::patch('/bookings/{booking}/cancel', [PortalController::class, 'cancelBooking']);
    Route::get('/tracking', [PortalController::class, 'tracking']);
    Route::get('/invoices', [PortalController::class, 'invoices']);
});
