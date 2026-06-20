<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(\App\Filament\Pages\Auth\Login::class)
            ->brandName('NEAMEE Auto-Tech')
            ->brandLogo(asset('images/logo/logo.png'))
            ->brandLogoHeight('2.75rem')
            ->favicon(asset('images/logo/logo.png'))
            ->darkMode(true, true)
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                'Operations',
                'CRM',
                'Inventory',
                'Website Content',
            ])
            ->colors([
                'primary' => [
                    50 => '#f4f6ef',
                    100 => '#e4e9d8',
                    200 => '#c9d4b3',
                    300 => '#a8b886',
                    400 => '#8a9a5f',
                    500 => '#6d7d47',
                    600 => '#556332',
                    700 => '#434f29',
                    800 => '#384024',
                    900 => '#2f3620',
                    950 => '#181c10',
                ],
                'gray' => [
                    50 => '#f6f7f8',
                    100 => '#eceef0',
                    200 => '#d5d9de',
                    300 => '#b0b8c1',
                    400 => '#8591a0',
                    500 => '#667384',
                    600 => '#515c6c',
                    700 => '#434b58',
                    800 => '#3a404b',
                    900 => '#333841',
                    950 => '#22252b',
                ],
                'success' => Color::Green,
                'warning' => Color::Amber,
                'danger' => Color::Red,
                'info' => Color::Sky,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                \App\Filament\Widgets\StatsOverview::class,
                \App\Filament\Widgets\RecentBookingsWidget::class,
            ])
            ->renderHook(
                'panels::head.end',
                fn (): string => Blade::render('<link rel="stylesheet" href="{{ asset(\'css/filament-admin.css\') }}">')
            )
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
