<?php

namespace App\Filament\Resources\StaffUserResource\Pages;

use App\Filament\Resources\StaffUserResource as StaffUserResourceClass;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStaffUsers extends ListRecords
{
    protected static string $resource = StaffUserResourceClass::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
