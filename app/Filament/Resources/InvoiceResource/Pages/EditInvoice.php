<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Filament\Support\InvoiceEmailUi;
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
        return InvoiceFormSchema::applyComputedTotals(InvoiceFormSchema::extractLaborFee(array_merge(
            $data,
            app(InvoiceService::class)->formLineData($this->record),
        )));
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data = InvoiceFormSchema::mergeLaborFeeIntoServiceLines($data);
        $this->lineData = [
            'part_lines' => $data['part_lines'] ?? [],
            'service_lines' => $data['service_lines'] ?? [],
        ];

        $data = InvoiceFormSchema::applyComputedTotals($data);
        unset($data['part_lines'], $data['service_lines'], $data['labor_fee']);

        return $data;
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
                ->url(fn (Invoice $record) => route('filament.admin.invoices.print', $record))
                ->openUrlInNewTab(),
            Actions\Action::make('send')
                ->label(fn (Invoice $record): string => InvoiceEmailUi::actionLabel($record->wasEmailedToCustomer()))
                ->icon('heroicon-o-paper-airplane')
                ->color(fn (Invoice $record): string => $record->wasEmailedToCustomer() ? 'warning' : 'success')
                ->requiresConfirmation()
                ->modalHeading(fn (Invoice $record): string => InvoiceEmailUi::modalHeading($record->wasEmailedToCustomer()))
                ->modalDescription(fn (Invoice $record): string => InvoiceEmailUi::confirmMessage($record->user?->email ?? 'the customer', $record->wasEmailedToCustomer()))
                ->visible(fn (Invoice $record) => in_array($record->status, ['draft', 'sent', 'overdue'], true))
                ->action(function (Invoice $record): void {
                    $wasResend = $record->wasEmailedToCustomer();

                    try {
                        app(InvoiceService::class)->sendToCustomer($record);
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Email failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->duration(12000)
                            ->send();

                        return;
                    }

                    $sent = $record->fresh();

                    Notification::make()
                        ->title(InvoiceEmailUi::successTitle($wasResend))
                        ->body(InvoiceEmailUi::successBody(
                            $sent->invoice_number,
                            $sent->user?->email ?? '',
                            $sent->wantsStripePayment() && ! $sent->isPaid(),
                        ))
                        ->success()
                        ->duration(12000)
                        ->send();
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
