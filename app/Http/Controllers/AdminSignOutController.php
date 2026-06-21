<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminSignOutController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        $frontend = rtrim(
            (string) config('app.frontend_url', env('FRONTEND_URL', 'https://neamee-autotechsolutions.com')),
            '/'
        );

        return redirect()->away($frontend.'/login?logout=1');
    }
}
