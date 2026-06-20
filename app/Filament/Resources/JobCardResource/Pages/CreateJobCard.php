<?php

namespace App\Filament\Resources\JobCardResource\Pages;

use App\Filament\Resources\JobCardResource;
use App\Services\JobCardWorkflowService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateJobCard extends CreateRecord
{
    protected static string $resource = JobCardResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        unset($data['line_items']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $lines = $this->form->getState()['line_items'] ?? [];

        if ($lines !== []) {
            app(JobCardWorkflowService::class)->syncLinesFromForm($this->record, $lines);
        }

        $invoice = app(JobCardWorkflowService::class)->autoInvoiceIfReady(
            $this->record->fresh(['lines', 'user', 'invoice', 'service'])
        );

        if ($invoice) {
            Notification::make()
                ->title('Invoice '.$invoice->invoice_number.' generated automatically')
                ->success()
                ->send();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}

