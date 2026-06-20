<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect(env('FRONTEND_URL', 'http://localhost:5173')));
