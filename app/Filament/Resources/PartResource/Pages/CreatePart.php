<?php

namespace App\Filament\Resources\PartResource\Pages;

use App\Filament\Resources\PartResource;
use App\Models\Part;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreatePart extends CreateRecord
{
    protected static string $resource = PartResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['part_no'] = filled($data['part_no'] ?? null)
            ? $data['part_no']
            : $this->generatePartNo();

        $data['sku'] = $data['part_no'];
        $data['name'] = filled($data['name'] ?? null)
            ? trim((string) $data['name'])
            : (trim(($data['brand'] ?? '').' '.($data['vehicle_model'] ?? '')) ?: ($data['brand'] ?? 'Part'));
        $data['min_stock'] = 0;
        $data['unit_cost'] = 0;
        $data['unit_price'] = $data['unit_price'] ?? 0;
        $data['quantity'] = $data['quantity'] ?? 0;
        $data['supplier_id'] = null;
        $data['is_active'] = $data['is_active'] ?? true;

        return $data;
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Part saved successfully')
            ->body('Part '.$this->record->part_no.' has been added to inventory.');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    private function generatePartNo(): string
    {
        $next = (int) Part::max('id') + 1;

        do {
            $partNo = 'P-'.str_pad((string) $next, 5, '0', STR_PAD_LEFT);
            $next++;
        } while (Part::where('part_no', $partNo)->orWhere('sku', $partNo)->exists());

        return $partNo;
    }
}
