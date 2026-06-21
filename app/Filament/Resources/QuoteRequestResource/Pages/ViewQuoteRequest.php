<?php

namespace App\Filament\Resources\QuoteRequestResource\Pages;

use App\Filament\Resources\QuoteRequestResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewQuoteRequest extends ViewRecord
{
    protected static string $resource = QuoteRequestResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Customer')->schema([
                Infolists\Components\TextEntry::make('name')->weight('bold'),
                Infolists\Components\TextEntry::make('email')->copyable(),
                Infolists\Components\TextEntry::make('phone'),
            ])->columns(3),
            Infolists\Components\Section::make('Request')->schema([
                Infolists\Components\TextEntry::make('service.name')->label('Service')->placeholder('General inquiry'),
                Infolists\Components\TextEntry::make('vehicle_make')->label('Make'),
                Infolists\Components\TextEntry::make('vehicle_model')->label('Model'),
                Infolists\Components\TextEntry::make('status')->badge(),
                Infolists\Components\TextEntry::make('message')->columnSpanFull(),
                Infolists\Components\TextEntry::make('created_at')->dateTime('M j, Y g:i A'),
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
