<?php

namespace App\Filament\Resources\JobCardResource\Pages;

use App\Filament\Resources\JobCardResource;
use App\Services\JobCardWorkflowService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditJobCard extends EditRecord
{
    protected static string $resource = JobCardResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['line_items'] = $this->record->lines()
            ->orderBy('id')
            ->get()
            ->map(fn ($line) => [
                'service_id' => $line->service_id,
                'description' => $line->description,
                'quantity' => $line->quantity,
                'unit_price' => $line->unit_price,
            ])
            ->all();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['line_items']);

        return $data;
    }

    protected function afterSave(): void
    {
        $lines = $this->form->getState()['line_items'] ?? [];

        app(JobCardWorkflowService::class)->syncLinesFromForm($this->record, $lines);

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

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

