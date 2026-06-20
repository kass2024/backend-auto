<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StaffOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->isStaff() ?? false;
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Customers', User::where('role', 'customer')->count())
                ->description('Monitor customer accounts')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Draft Invoices', Invoice::where('status', 'draft')->count())
                ->description('Ready to send')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning'),

            Stat::make('Outstanding', '$'.number_format(Invoice::whereIn('status', ['sent', 'overdue'])->sum('total'), 2))
                ->description('Awaiting payment')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('danger'),
        ];
    }
}
