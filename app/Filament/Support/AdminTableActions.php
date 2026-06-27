<?php

namespace App\Filament\Support;

use App\Models\Invoice;
use App\Services\InvoiceService;
use App\Filament\Support\InvoiceEmailUi;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AdminTableActions
{
    /**
     * Browser confirm via Livewire — avoids Filament modal/backdrop conflicts with bulk actions.
     *
     * @return array<string, string>
     */
    private static function confirm(string $message): array
    {
        return ['wire:confirm' => $message];
    }

    public static function delete(string $itemLabel): Action
    {
        $label = Str::lower($itemLabel);

        return Action::make('deleteRecord')
            ->label('Delete')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation(false)
            ->extraAttributes(self::confirm('Delete this '.$label.'? This cannot be undone.'))
            ->action(function (Model $record, Action $action) use ($label): void {
                $record->delete();
                $action->success();
            })
            ->successNotification(
                Notification::make()
                    ->success()
                    ->title(ucfirst($label).' deleted successfully')
                    ->body('The '.$label.' has been removed.')
            );
    }

    public static function deleteBulk(string $itemLabel): DeleteBulkAction
    {
        $label = Str::lower($itemLabel);
        $plural = Str::plural($label);

        return DeleteBulkAction::make('deleteSelected')
            ->label('Delete selected')
            ->requiresConfirmation(false)
            ->extraAttributes(self::confirm('Delete selected '.$plural.'? This cannot be undone.'))
            ->successNotification(
                Notification::make()
                    ->success()
                    ->title(ucfirst($plural).' deleted successfully')
                    ->body('Selected '.$plural.' have been removed.')
            );
    }

    public static function sendInvoiceEmail(): Action
    {
        return Action::make('emailCustomer')
            ->label(fn (Invoice $record): string => InvoiceEmailUi::actionLabel($record->wasEmailedToCustomer()))
            ->icon('heroicon-o-paper-airplane')
            ->color(fn (Invoice $record): string => $record->wasEmailedToCustomer() ? 'warning' : 'success')
            ->requiresConfirmation(false)
            ->extraAttributes(self::confirm('Email or resend this invoice to the customer?'))
            ->action(function (Invoice $record, Action $action): void {
                try {
                    app(InvoiceService::class)->sendToCustomer($record);
                    $action->success();
                } catch (\Throwable $e) {
                    $action->failureNotification(
                        Notification::make()
                            ->danger()
                            ->title('Email failed')
                            ->body($e->getMessage())
                    );
                    $action->failure();
                }
            })
            ->successNotification(
                Notification::make()
                    ->success()
                    ->title('Invoice email sent')
                    ->body('The customer will receive the invoice shortly. Check inbox and spam folder.')
            );
    }

    public static function markInvoicePaid(): Action
    {
        return Action::make('markInvoicePaid')
            ->label('Mark Paid')
            ->icon('heroicon-o-check-circle')
            ->color('primary')
            ->requiresConfirmation(false)
            ->extraAttributes(self::confirm('Mark this invoice as paid?'))
            ->action(function (Invoice $record, Action $action): void {
                try {
                    app(InvoiceService::class)->markPaid($record);
                    $action->success();
                } catch (\Throwable $e) {
                    $action->failureNotification(
                        Notification::make()
                            ->danger()
                            ->title('Could not mark invoice as paid')
                            ->body($e->getMessage())
                    );
                    $action->failure();
                }
            })
            ->successNotification(
                Notification::make()
                    ->success()
                    ->title('Invoice marked as paid')
                    ->body('The invoice status has been updated.')
            );
    }

    public static function markInvoiceUnpaid(): Action
    {
        return Action::make('markInvoiceUnpaid')
            ->label('Mark Unpaid')
            ->icon('heroicon-o-x-circle')
            ->color('warning')
            ->requiresConfirmation(false)
            ->extraAttributes(self::confirm('Mark this invoice as unpaid?'))
            ->action(function (Invoice $record, Action $action): void {
                try {
                    app(InvoiceService::class)->markUnpaid($record);
                    $action->success();
                } catch (\Throwable $e) {
                    $action->failureNotification(
                        Notification::make()
                            ->danger()
                            ->title('Could not mark invoice as unpaid')
                            ->body($e->getMessage())
                    );
                    $action->failure();
                }
            })
            ->successNotification(
                Notification::make()
                    ->success()
                    ->title('Invoice marked as unpaid')
                    ->body('The invoice status has been updated.')
            );
    }
}
