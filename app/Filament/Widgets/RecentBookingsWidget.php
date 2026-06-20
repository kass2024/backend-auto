<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentBookingsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Recent Bookings';

    protected static ?string $description = 'Latest service appointments across the garage';

    public static function canView(): bool
    {
        return auth()->user()?->isFullAdmin() ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Booking::query()->with(['service', 'user'])->latest()->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->label('Ref')
                    ->searchable()
                    ->weight('bold')
                    ->color('primary'),
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable(),
                Tables\Columns\TextColumn::make('service.name')
                    ->label('Service')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('scheduled_date')
                    ->date('M j, Y')
                    ->label('Date')
                    ->sortable(),
                Tables\Columns\TextColumn::make('scheduled_time')
                    ->time('g:i A')
                    ->label('Time')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'success',
                        'cancelled' => 'danger',
                        'completed' => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
            ])
            ->striped()
            ->emptyStateHeading('No bookings yet')
            ->emptyStateDescription('New appointments will appear here.')
            ->emptyStateIcon('heroicon-o-calendar-days')
            ->paginated(false);
    }
}
