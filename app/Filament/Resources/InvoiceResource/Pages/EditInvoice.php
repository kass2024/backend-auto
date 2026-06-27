<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Filament\Support\InvoiceFormSchema;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    protected array $lineData = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return InvoiceFormSchema::applyComputedTotals(array_merge(
            $data,
            app(InvoiceService::class)->formLineData($this->record),
        ));
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->lineData = [
            'part_lines' => $data['part_lines'] ?? [],
            'service_lines' => $data['service_lines'] ?? [],
        ];

        unset($data['part_lines'], $data['service_lines']);

        return InvoiceFormSchema::applyComputedTotals($data);
    }

    protected function afterSave(): void
    {
        $invoiceService = app(InvoiceService::class);

        $invoiceService->syncLineItems(
            $this->record,
            $this->lineData['part_lines'] ?? [],
            $this->lineData['service_lines'] ?? [],
        );
        $invoiceService->recalculateTotals($this->record);
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Invoice saved')
            ->body('Invoice '.$this->record->invoice_number.' has been updated.');
    }

    protected function getRedirectUrl(): ?string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('print')
                ->label('Print invoice')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(fn (Invoice $record) => route('filament.admin.invoice.print', $record))
                ->openUrlInNewTab(),
            Actions\Action::make('send')
                ->label('Email Customer')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (Invoice $record) => in_array($record->status, ['draft', 'sent', 'overdue'], true))
                ->action(function (Invoice $record) {
                    try {
                        app(InvoiceService::class)->sendToCustomer($record);
                        Notification::make()->title('Invoice emailed successfully')->success()->send();
                    } catch (\Throwable $e) {
                        Notification::make()->title('Email failed')->body($e->getMessage())->danger()->send();
                    }
                }),
            Actions\Action::make('markPaid')
                ->label('Mark Paid')
                ->icon('heroicon-o-check-circle')
                ->visible(fn (Invoice $record) => $record->status !== 'paid')
                ->requiresConfirmation()
                ->action(function (Invoice $record) {
                    app(InvoiceService::class)->markPaid($record);
                    Notification::make()->title('Invoice marked as paid')->success()->send();
                }),
            Actions\DeleteAction::make(),
        ];
    }
}
