<?php

namespace App\Support;

class FrontendUrl
{
    public static function base(): string
    {
        return rtrim((string) config('app.frontend_url'), '/');
    }

    public static function to(string $path = ''): string
    {
        $path = ltrim($path, '/');

        return $path === '' ? self::base() : self::base().'/'.$path;
    }

    public static function login(): string
    {
        return self::to('login');
    }

    public static function portal(string $path = ''): string
    {
        return self::to('portal'.($path !== '' ? '/'.ltrim($path, '/') : ''));
    }
}
