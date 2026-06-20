<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\GalleryImage;
use App\Models\Promotion;
use App\Models\Service;
use App\Models\Testimonial;

class PublicController extends Controller
{
    public function health()
    {
        $dbOk = false;
        $dbError = null;

        try {
            \Illuminate\Support\Facades\DB::connection()->getPdo();
            $dbOk = true;
        } catch (\Throwable $e) {
            $dbError = $e->getMessage();
        }

        $appKeySet = filled(config('app.key'));
        $sessionDriver = config('session.driver');
        $sessionsTable = config('session.table', 'sessions');
        $sessionsTableOk = $sessionDriver !== 'database' || \Illuminate\Support\Facades\Schema::hasTable($sessionsTable);
        $sessionPathWritable = $sessionDriver !== 'file'
            || is_writable(storage_path('framework/sessions'));

        $authReady = $appKeySet && $sessionsTableOk && $sessionPathWritable;
        $sessionDomain = config('session.domain');

        return response()->json([
            'ok' => $dbOk && $authReady,
            'app' => config('app.name'),
            'env' => config('app.env'),
            'url' => config('app.url'),
            'database' => $dbOk ? 'connected' : 'failed',
            'db_error' => app()->hasDebugModeEnabled() ? $dbError : ($dbOk ? null : 'Database connection failed'),
            'admin_ready' => $dbOk && \Illuminate\Support\Facades\Schema::hasTable('users')
                ? \App\Models\User::where('email', 'admin@neamee-autotechsolutions.com')->exists()
                : false,
            'auth_ready' => $authReady,
            'app_key_set' => $appKeySet,
            'session_driver' => $sessionDriver,
            'session_domain' => $sessionDomain ?: null,
            'session_domain_ok' => filled($sessionDomain) && str_starts_with($sessionDomain, '.'),
            'sessions_table_ok' => $sessionsTableOk,
            'session_path_writable' => $sessionPathWritable,
            'time' => now()->toIso8601String(),
        ], ($dbOk && $authReady) ? 200 : 503);
    }

    public function home()
    {
        return response()->json([
            'services' => Service::where('is_active', true)->orderBy('sort_order')->get(),
            'promotions' => Promotion::active()->where('is_featured', true)->latest()->take(3)->get(),
            'testimonials' => Testimonial::where('is_active', true)->where('is_featured', true)->latest()->take(6)->get(),
            'gallery' => GalleryImage::where('is_active', true)->orderBy('sort_order')->take(8)->get(),
            'blog_posts' => BlogPost::published()->latest('published_at')->take(3)->get(),
            'contact' => [
                'address' => '120 Bogle Lane',
                'city' => 'Bowling Green',
                'state' => 'KY',
                'zip' => '42101',
                'email' => 'info@neamee-autotechsolutions.com',
                'phone' => '+1 (567) 329-9231',
                'whatsapp' => '15673299231',
            ],
        ]);
    }

    public function services()
    {
        return response()->json([
            'services' => Service::where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }
}
