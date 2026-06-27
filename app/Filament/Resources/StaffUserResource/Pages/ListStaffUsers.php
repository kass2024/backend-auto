<?php

namespace App\Filament\Resources\StaffUserResource\Pages;

use App\Filament\Pages\BasePrintableListRecords;
use App\Filament\Resources\StaffUserResource;
use Filament\Actions;

class ListStaffUsers extends BasePrintableListRecords
{
    protected static string $resource = StaffUserResource::class;

    protected function getListDocumentTitle(): string
    {
        return 'STAFF LIST';
    }

    protected function getListPrintKey(): string
    {
        return 'staff';
    }

    protected function getResourceHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
