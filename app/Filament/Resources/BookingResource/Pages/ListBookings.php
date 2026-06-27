<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Pages\BasePrintableListRecords;
use App\Filament\Resources\BookingResource;
use Filament\Actions;

class ListBookings extends BasePrintableListRecords
{
    protected static string $resource = BookingResource::class;

    protected function getListDocumentTitle(): string
    {
        return 'BOOKINGS LIST';
    }

    protected function getListPrintKey(): string
    {
        return 'bookings';
    }

    protected function getResourceHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
