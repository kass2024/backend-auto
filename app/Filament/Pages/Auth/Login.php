<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;

class Login extends BaseLogin
{
    public function mount(): void
    {
        if (auth()->check() && auth()->user()->isAdmin()) {
            redirect()->intended(filament()->getUrl());

            return;
        }

        $frontend = rtrim(config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:5173')), '/');

        redirect()->away($frontend.'/login');
    }
}
