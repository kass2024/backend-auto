<?php

/**
 * Laravel front controller — cPanel (NEAMEE Auto-Tech API)
 *
 * Upload this file as public/index.php on the server, OR copy:
 *   cp public/index.cpanel.php public/index.php
 *
 * Recommended: set cPanel document root to the public folder:
 *   /home/you/api.neamee-autotechsolutions.com/public
 *
 * If document root is the Laravel project root, root /.htaccess forwards
 * here — the ../ paths below are correct for both layouts.
 */

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| cPanel reverse proxy / SSL termination
|--------------------------------------------------------------------------
|
| cPanel often terminates SSL at the proxy. Laravel needs HTTPS detected
| correctly for URLs, cookies, and Sanctum.
|
*/

if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
    $_SERVER['SERVER_PORT'] = '443';
}

if (! empty($_SERVER['HTTP_X_FORWARDED_HOST'])) {
    $_SERVER['HTTP_HOST'] = $_SERVER['HTTP_X_FORWARDED_HOST'];
}

/*
|--------------------------------------------------------------------------
| Check If The Application Is Under Maintenance
|--------------------------------------------------------------------------
*/

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
*/

require __DIR__.'/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
*/

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
