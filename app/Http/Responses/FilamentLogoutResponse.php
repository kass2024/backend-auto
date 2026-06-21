<?php

namespace App\Http\Responses;

use Filament\Http\Responses\Auth\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Http\RedirectResponse;

class FilamentLogoutResponse implements LogoutResponseContract
{
    public function toResponse($request): RedirectResponse
    {
        $frontend = rtrim((string) config('app.frontend_url'), '/');

        return redirect()->away($frontend.'/login?logout=1');
    }
}
