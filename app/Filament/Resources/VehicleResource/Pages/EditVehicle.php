<?php

namespace App\Filament\Resources\VehicleResource\Pages;

use App\Filament\Resources\VehicleResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditVehicle extends EditRecord
{
    protected static string $resource = VehicleResource::class;

    protected function getSavedNotification(): ?Notification
    {
        $label = trim(($this->record->plate_number ?? '').' — '.($this->record->display_name ?? ''));

        return Notification::make()
            ->success()
            ->title('Vehicle saved successfully')
            ->body($label !== '—' ? $label : 'Vehicle updated.');
    }

    protected function getRedirectUrl(): ?string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
