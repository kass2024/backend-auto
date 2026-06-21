<?php

namespace App\Providers;

use App\Http\Responses\FilamentLogoutResponse;
use Filament\Http\Responses\Auth\Contracts\LogoutResponse as FilamentLogoutResponseContract;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(FilamentLogoutResponseContract::class, FilamentLogoutResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        if ($this->app->runningInConsole() === false) {
            \App\Services\DatabaseBootstrapper::run();
        }
    }
}
