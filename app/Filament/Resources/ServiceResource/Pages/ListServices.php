<?php

namespace App\Filament\Resources\ServiceResource\Pages;

use App\Filament\Pages\BasePrintableListRecords;
use App\Filament\Resources\ServiceResource;
use Filament\Actions;

class ListServices extends BasePrintableListRecords
{
    protected static string $resource = ServiceResource::class;

    protected function getListDocumentTitle(): string
    {
        return 'SERVICES CATALOG';
    }

    protected function getListPrintKey(): string
    {
        return 'services';
    }

    protected function getResourceHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
