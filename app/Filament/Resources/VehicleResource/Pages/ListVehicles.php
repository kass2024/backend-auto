<?php

namespace App\Filament\Resources\VehicleResource\Pages;

use App\Filament\Pages\BasePrintableListRecords;
use App\Filament\Resources\VehicleResource;
use Filament\Actions;

class ListVehicles extends BasePrintableListRecords
{
    protected static string $resource = VehicleResource::class;

    protected function getListDocumentTitle(): string
    {
        return 'VEHICLE REGISTER';
    }

    protected function getListPrintKey(): string
    {
        return 'vehicles';
    }

    protected function getResourceHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
