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

        // Guest — show Filament login (e.g. after failed redirect). Staff can also use main site /login.
        parent::mount();
    }
}
