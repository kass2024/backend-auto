<?php

namespace App\Filament\Resources\VehicleResource\Pages;

use App\Filament\Resources\VehicleResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateVehicle extends CreateRecord
{
    protected static string $resource = VehicleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = null;
        $data['vin'] = null;

        return $data;
    }

    protected function getCreatedNotification(): ?Notification
    {
        $label = trim(($this->record->plate_number ?? '').' — '.($this->record->display_name ?? ''));

        return Notification::make()
            ->success()
            ->title('Vehicle saved successfully')
            ->body($label !== '—' ? $label : 'Vehicle added to the catalog.');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
