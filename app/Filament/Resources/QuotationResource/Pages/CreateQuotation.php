<?php

namespace App\Filament\Resources\QuotationResource\Pages;

use App\Filament\Resources\QuotationResource;
use App\Services\QuotationService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateQuotation extends CreateRecord
{
    protected static string $resource = QuotationResource::class;

    protected bool $shouldEmailCustomer = true;

    protected array $lineData = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->shouldEmailCustomer = (bool) ($data['send_to_customer'] ?? true);
        $this->lineData = [
            'part_lines' => $data['part_lines'] ?? [],
            'labor_lines' => $data['labor_lines'] ?? [],
            'additional_lines' => $data['additional_lines'] ?? [],
        ];

        unset(
            $data['send_to_customer'],
            $data['part_lines'],
            $data['labor_lines'],
            $data['additional_lines'],
        );

        return $data;
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        return app(QuotationService::class)->createFromForm(
            $data,
            $this->lineData['part_lines'] ?? [],
            $this->lineData['labor_lines'] ?? [],
            $this->lineData['additional_lines'] ?? [],
        );
    }

    protected function afterCreate(): void
    {
        if (! $this->shouldEmailCustomer) {
            Notification::make()
                ->title('Smart quote created')
                ->body('Quote '.$this->record->quote_number.' saved as draft.')
                ->success()
                ->send();

            return;
        }

        try {
            app(QuotationService::class)->sendToCustomer($this->record->fresh());
            Notification::make()
                ->title('Smart quote created')
                ->body('Quote '.$this->record->quote_number.' saved. Email is being sent to the customer.')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Quote saved — email not sent')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
