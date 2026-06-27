<?php

use App\Http\Controllers\AdminSignOutController;
use App\Http\Controllers\InvoicePaymentController;
use App\Http\Controllers\InvoiceViewController;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect(\App\Support\FrontendUrl::base()));

Route::middleware('web')->match(['get', 'post'], '/admin/sign-out', AdminSignOutController::class)
    ->name('admin.sign-out');

Route::get('/pay/invoice/{invoice}', [InvoicePaymentController::class, 'pay'])->name('invoice.pay');
Route::get('/invoice/{invoice}/view', InvoiceViewController::class)->name('invoice.view');
Route::get('/pay/invoice/{invoice}/success', [InvoicePaymentController::class, 'success'])->name('invoice.payment.success');
Route::get('/pay/invoice/{invoice}/cancel', [InvoicePaymentController::class, 'cancel'])->name('invoice.payment.cancel');
Route::post('/stripe/webhook', [InvoicePaymentController::class, 'webhook'])
    ->name('stripe.webhook')
    ->withoutMiddleware([VerifyCsrfToken::class]);
