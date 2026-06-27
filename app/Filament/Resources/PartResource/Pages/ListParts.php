<?php

namespace App\Filament\Resources\PartResource\Pages;

use App\Filament\Pages\BasePrintableListRecords;
use App\Filament\Resources\PartResource;
use Filament\Actions;

class ListParts extends BasePrintableListRecords
{
    protected static string $resource = PartResource::class;

    protected function getListDocumentTitle(): string
    {
        return 'AUTOMOTIVE PARTS INVENTORY';
    }

    protected function getListPrintKey(): string
    {
        return 'parts';
    }

    protected function getResourceHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
