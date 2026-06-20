<?php

use App\Http\Controllers\Api\PublicController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API health check — always loaded (cPanel-safe)
|--------------------------------------------------------------------------
| Registered separately from routes/api.php so deploy/sync cannot omit it.
*/
Route::middleware('api')->get('/api/public/health', [PublicController::class, 'health']);
