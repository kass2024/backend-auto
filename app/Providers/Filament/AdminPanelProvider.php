<?php

namespace App\Providers\Filament;

use App\Http\Controllers\Admin\InvoicePrintController;
use App\Http\Controllers\Admin\ListPrintController;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
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
            ->databaseNotifications()
            ->databaseNotificationsPolling('60s')
            ->sidebarCollapsibleOnDesktop()
            ->userMenuItems([
                'logout' => \Filament\Navigation\MenuItem::make()->hidden(),
                'sign-out' => \Filament\Navigation\MenuItem::make()
                    ->label('Sign out')
                    ->icon('heroicon-m-arrow-left-on-rectangle')
                    ->url(fn (): string => url('/admin/sign-out')),
            ])
            ->renderHook(
                'panels::topbar.end',
                fn (): string => Blade::render(
                    '<a href="{{ url(\'/admin/sign-out\') }}"'
                    .' class="neamee-sign-out-btn inline-flex items-center gap-1.5 rounded-lg border border-white/15 px-3 py-2 text-sm font-medium text-gray-200 hover:border-red-400/60 hover:text-white transition shrink-0">'
                    .'<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>'
                    .'Sign out</a>'
                )
            )
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
                \App\Filament\Widgets\RecentQuoteRequestsWidget::class,
                \App\Filament\Widgets\ServicesCatalogWidget::class,
                \App\Filament\Widgets\StaffOverviewWidget::class,
            ])
            ->renderHook(
                'panels::head.end',
                fn (): string => Blade::render(
                    '<link rel="stylesheet" href="{{ asset(\'css/filament-admin.css\') }}?v=10">'
                )
            )
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->routes(function () {
                Route::middleware([Authenticate::class])
                    ->get('/lists/print/{key}', ListPrintController::class)
                    ->name('list.print');
                Route::middleware([Authenticate::class])
                    ->get('/invoices/{invoice}/print', InvoicePrintController::class)
                    ->name('invoice.print');
            });
    }
}
