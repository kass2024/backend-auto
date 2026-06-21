<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\QuoteRequestResource;
use App\Models\QuoteRequest;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentQuoteRequestsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Recent Quote Requests';

    protected static ?string $description = 'Latest quote inquiries from the website';

    public static function canView(): bool
    {
        return auth()->user()?->isFullAdmin() ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                QuoteRequest::query()->with('service')->latest()->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('phone'),
                Tables\Columns\TextColumn::make('service.name')
                    ->label('Service')
                    ->placeholder('General')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'new' => 'info',
                        'contacted' => 'warning',
                        'quoted' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('M j, g:i A')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('open')
                    ->label('View')
                    ->icon('heroicon-m-eye')
                    ->url(fn (QuoteRequest $record) => QuoteRequestResource::getUrl('view', ['record' => $record])),
            ])
            ->striped()
            ->emptyStateHeading('No quote requests yet')
            ->emptyStateIcon('heroicon-o-inbox')
            ->paginated(false);
    }
}
