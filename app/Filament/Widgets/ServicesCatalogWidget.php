<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ServiceResource;
use App\Filament\Support\Money;
use App\Models\Service;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ServicesCatalogWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Services Catalog';

    protected static ?string $description = 'All services shown on the public website — drag to reorder in Services list';

    public static function canView(): bool
    {
        return auth()->user()?->isFullAdmin() ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Service::query()->orderBy('sort_order'))
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')->label('#')->alignCenter(),
                Tables\Columns\TextColumn::make('name')->weight('bold')->searchable(),
                Tables\Columns\TextColumn::make('price_from')
                    ->label('From')
                    ->formatStateUsing(fn ($state) => Money::format($state)),
                Tables\Columns\TextColumn::make('duration_minutes')
                    ->label('Duration')
                    ->formatStateUsing(fn ($state) => $state ? "{$state} min" : '—'),
                Tables\Columns\TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state) => $state ? 'Active' : 'Hidden')
                    ->color(fn (bool $state) => $state ? 'success' : 'gray'),
            ])
            ->actions([
                Tables\Actions\Action::make('open')
                    ->label('Edit')
                    ->icon('heroicon-m-pencil-square')
                    ->url(fn (Service $record) => ServiceResource::getUrl('edit', ['record' => $record])),
            ])
            ->striped()
            ->paginated(false);
    }
}
