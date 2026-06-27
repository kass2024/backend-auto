<?php

namespace App\Http\Middleware;

use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful as SanctumMiddleware;

class EnsureFrontendRequestsAreStateful extends SanctumMiddleware
{
    /**
     * Vite dev proxy serves /api on the same origin as the SPA (localhost:5173),
     * so browsers omit Origin/Referer and Sanctum would skip session middleware.
     */
    public static function fromFrontend($request)
    {
        if (parent::fromFrontend($request)) {
            return true;
        }

        return app()->environment('local')
            && $request->headers->get('X-Requested-With') === 'XMLHttpRequest';
    }
}
