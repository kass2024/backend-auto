<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
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
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        app(InvoiceService::class)->recalculateTotals($this->record);
    }
}
