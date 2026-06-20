<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Services\InvoiceService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['invoice_number'])) {
            $data['invoice_number'] = app(InvoiceService::class)->generateNumber();
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        app(InvoiceService::class)->recalculateTotals($this->record);
    }
}
