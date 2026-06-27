<?php

namespace App\Filament\Resources\PartResource\Pages;

use App\Filament\Resources\PartResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPart extends EditRecord
{
    protected static string $resource = PartResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['supplier_id'] = null;
        $data['unit_price'] = $data['unit_price'] ?? 0;
        $data['quantity'] = $data['quantity'] ?? 0;
        $data['name'] = trim(($data['brand'] ?? '').' '.($data['vehicle_model'] ?? '')) ?: ($data['brand'] ?? $this->record->name);

        if (filled($data['part_no'] ?? null)) {
            $data['sku'] = $data['part_no'];
        }

        return $data;
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Part saved successfully')
            ->body('"'.$this->record->name.'" has been updated.');
    }

    protected function getRedirectUrl(): ?string
    {
        return $this->getResource()::getUrl('index');
    }
}
