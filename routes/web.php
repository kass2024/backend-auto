<?php

use App\Http\Controllers\AdminSignOutController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect(env('FRONTEND_URL', 'http://localhost:5173')));

Route::middleware('web')->match(['get', 'post'], '/admin/sign-out', AdminSignOutController::class)
    ->name('admin.sign-out');
