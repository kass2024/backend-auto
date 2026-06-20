<?php

namespace App\Filament\Resources\StaffUserResource\Pages;

use App\Filament\Resources\StaffUserResource as StaffUserResourceClass;
use Filament\Resources\Pages\CreateRecord;

class CreateStaffUser extends CreateRecord
{
    protected static string $resource = StaffUserResourceClass::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['role'] = 'staff';

        return $data;
    }
}
