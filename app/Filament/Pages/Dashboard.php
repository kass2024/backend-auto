<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?int $navigationSort = -2;

    public function getHeading(): string
    {
        return 'Garage Command Center';
    }

    public function getSubheading(): ?string
    {
        return 'NEAMEE Auto-Tech Solutions — Bowling Green, KY';
    }
}
