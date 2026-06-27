<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Filament\Support\InvoiceFormSchema;
use App\Services\InvoiceService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected bool $shouldEmailCustomer = true;

    protected array $lineData = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['invoice_number'])) {
            $data['invoice_number'] = app(InvoiceService::class)->generateNumber();
        }

        $this->shouldEmailCustomer = (bool) ($data['send_to_customer'] ?? true);
        $this->lineData = [
            'part_lines' => $data['part_lines'] ?? [],
            'service_lines' => $data['service_lines'] ?? [],
        ];

        unset($data['send_to_customer'], $data['part_lines'], $data['service_lines']);

        return InvoiceFormSchema::applyComputedTotals($data);
    }

    protected function afterCreate(): void
    {
        $invoiceService = app(InvoiceService::class);

        $invoiceService->syncLineItems(
            $this->record,
            $this->lineData['part_lines'] ?? [],
            $this->lineData['service_lines'] ?? [],
        );
        $invoiceService->recalculateTotals($this->record);

        if ($this->shouldEmailCustomer && $this->record->status !== 'draft') {
            try {
                $invoiceService->sendToCustomer($this->record->fresh());
            } catch (\Throwable $e) {
                Notification::make()
                    ->warning()
                    ->title('Invoice saved — email not sent')
                    ->body($e->getMessage())
                    ->send();

                return;
            }

            Notification::make()
                ->success()
                ->title('Invoice created and emailed')
                ->body('Invoice '.$this->record->invoice_number.' was sent to '.$this->record->user?->email.' with payment link.')
                ->send();

            return;
        }

        Notification::make()
            ->success()
            ->title('Invoice created')
            ->body('Invoice '.$this->record->invoice_number.' saved successfully.')
            ->send();
    }

    protected function getCreatedNotification(): ?Notification
    {
        return null;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
