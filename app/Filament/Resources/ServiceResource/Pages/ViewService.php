<?php

namespace App\Filament\Resources\ServiceResource\Pages;

use App\Filament\Resources\ServiceResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Support\Money;

class ViewService extends ViewRecord
{
    protected static string $resource = ServiceResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Service details')->schema([
                Infolists\Components\TextEntry::make('name')->weight('bold')->size('lg'),
                Infolists\Components\TextEntry::make('slug'),
                Infolists\Components\TextEntry::make('price_from')->label('Starting price')
                    ->formatStateUsing(fn ($state) => Money::format($state)),
                Infolists\Components\TextEntry::make('duration_minutes')->suffix(' minutes'),
                Infolists\Components\TextEntry::make('sort_order')->label('List order'),
                Infolists\Components\IconEntry::make('is_active')->label('Live on website')->boolean(),
                Infolists\Components\TextEntry::make('description')->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
