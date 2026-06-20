<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];

    /**
     * Accept plain X-XSRF-TOKEN from axios (unencrypted XSRF-TOKEN cookie).
     */
    protected function getTokenFromRequest($request)
    {
        $token = parent::getTokenFromRequest($request);

        if ($token) {
            return $token;
        }

        $header = $request->header('X-XSRF-TOKEN');

        return is_string($header) && $header !== '' ? $header : null;
    }
}
