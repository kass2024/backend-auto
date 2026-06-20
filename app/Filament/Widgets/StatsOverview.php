<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\Invoice;
use App\Models\JobCard;
use App\Models\Part;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->isFullAdmin() ?? false;
    }

    protected function getStats(): array
    {
        $monthlyRevenue = Invoice::where('status', 'paid')
            ->whereMonth('paid_at', now()->month)
            ->sum('total');

        $outstanding = Invoice::whereIn('status', ['sent', 'overdue'])->sum('total');

        return [
            Stat::make('Total Customers', User::where('role', 'customer')->count())
                ->description('Registered customers')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary')
                ->chart([2, 4, 3, 5, 4, 6, User::where('role', 'customer')->count() ?: 1]),

            Stat::make('Active Repairs', JobCard::whereNotIn('status', ['delivered'])->count())
                ->description('Vehicles in service')
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color('warning')
                ->chart([1, 2, 1, 3, 2, 2, JobCard::whereNotIn('status', ['delivered'])->count() ?: 0]),

            Stat::make('Pending Bookings', Booking::where('status', 'pending')->count())
                ->description('Awaiting confirmation')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info')
                ->chart([0, 1, 0, 2, 1, 0, Booking::where('status', 'pending')->count()]),

            Stat::make('Monthly Revenue', '$' . number_format($monthlyRevenue, 2))
                ->description('Paid invoices this month')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->chart([120, 340, 280, 450, 380, 520, (float) $monthlyRevenue]),

            Stat::make('Outstanding', '$' . number_format($outstanding, 2))
                ->description('Unpaid invoices')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger')
                ->chart([50, 80, 120, 90, 150, 201, (float) $outstanding]),

            Stat::make('Low Stock', Part::whereColumn('quantity', '<=', 'min_stock')->count())
                ->description('Parts need reorder')
                ->descriptionIcon('heroicon-m-cube')
                ->color('gray')
                ->chart([0, 1, 0, 0, 2, 1, Part::whereColumn('quantity', '<=', 'min_stock')->count()]),
        ];
    }
}
