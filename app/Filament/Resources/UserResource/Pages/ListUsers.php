<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Pages\BasePrintableListRecords;
use App\Filament\Resources\UserResource;
use Filament\Actions;

class ListUsers extends BasePrintableListRecords
{
    protected static string $resource = UserResource::class;

    protected function getListDocumentTitle(): string
    {
        return 'CUSTOMER LIST';
    }

    protected function getListPrintKey(): string
    {
        return 'customers';
    }

    protected function getResourceHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
