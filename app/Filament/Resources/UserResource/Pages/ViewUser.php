<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Customer')->schema([
                Infolists\Components\TextEntry::make('name'),
                Infolists\Components\TextEntry::make('email')->copyable(),
                Infolists\Components\TextEntry::make('phone'),
                Infolists\Components\TextEntry::make('address')->columnSpanFull(),
                Infolists\Components\TextEntry::make('loyalty_points')->label('Loyalty Points'),
                Infolists\Components\TextEntry::make('created_at')->dateTime(),
            ])->columns(2),
        ]);
    }
}
