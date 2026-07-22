<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Filament\Support\InvoiceFlashNotifications;
use App\Filament\Support\Money;
use App\Filament\Support\InvoiceEmailUi;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Invoice')->schema([
                Infolists\Components\TextEntry::make('invoice_number')->label('Invoice #'),
                Infolists\Components\TextEntry::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'paid' => 'Paid',
                        'sent' => 'Unpaid',
                        'overdue' => 'Overdue',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'sent', 'overdue' => 'warning',
                        'draft' => 'gray',
                        default => 'gray',
                    }),
                Infolists\Components\TextEntry::make('user.name')->label('Customer'),
                Infolists\Components\TextEntry::make('vehicle.plate_number')->label('Vehicle')->placeholder('—'),
                Infolists\Components\TextEntry::make('due_date')->date(),
                Infolists\Components\TextEntry::make('paid_at')->dateTime()->placeholder('—'),
                Infolists\Components\TextEntry::make('customer_emailed_at')
                    ->label('Emailed to customer')
                    ->dateTime()
                    ->placeholder('Not yet sent')
                    ->visible(fn (): bool => \Illuminate\Support\Facades\Schema::hasColumn('invoices', 'customer_emailed_at')),
                Infolists\Components\TextEntry::make('total')->formatStateUsing(fn ($state) => Money::format($state)),
            ])->columns(3),
            Infolists\Components\Section::make('Parts used')->schema([
                Infolists\Components\RepeatableEntry::make('partItems')
                    ->label('')
                    ->schema([
                        Infolists\Components\TextEntry::make('part_number')->label('Part no.'),
                        Infolists\Components\TextEntry::make('description')->label('Part name'),
                        Infolists\Components\TextEntry::make('quantity'),
                        Infolists\Components\TextEntry::make('total')->formatStateUsing(fn ($state) => Money::format($state)),
                    ])
                    ->columns(4)
                    ->placeholder('No parts listed'),
            ]),
            Infolists\Components\Section::make('Labor')->schema([
                Infolists\Components\RepeatableEntry::make('serviceItems')
                    ->label('')
                    ->schema([
                        Infolists\Components\TextEntry::make('description')->columnSpan(2),
                        Infolists\Components\TextEntry::make('quantity'),
                        Infolists\Components\TextEntry::make('total')->formatStateUsing(fn ($state) => Money::format($state)),
                    ])
                    ->columns(4)
                    ->placeholder('No labor listed'),
                Infolists\Components\TextEntry::make('work_description')
                    ->label('Description of work')
                    ->placeholder('—')
                    ->columnSpanFull(),
            ]),
            Infolists\Components\Section::make('Totals')->schema([
                Infolists\Components\TextEntry::make('parts_total')->label('Parts')->formatStateUsing(fn ($state) => Money::format($state)),
                Infolists\Components\TextEntry::make('labor_total')->label('Labor')->formatStateUsing(fn ($state) => Money::format($state)),
                Infolists\Components\TextEntry::make('tax_amount')->formatStateUsing(fn ($state) => Money::format($state)),
                Infolists\Components\TextEntry::make('discount')->formatStateUsing(fn ($state) => Money::format($state)),
                Infolists\Components\TextEntry::make('total')->formatStateUsing(fn ($state) => Money::format($state))->weight('bold'),
            ])->columns(3),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('print')
                ->label('Print invoice')
                ->icon('heroicon-o-printer')
                ->url(fn (Invoice $record) => route('filament.admin.invoices.print', $record))
                ->openUrlInNewTab(),
            Actions\Action::make('emailCustomer')
                ->label(fn (Invoice $record): string => InvoiceEmailUi::actionLabel($record->wasEmailedToCustomer()))
                ->icon('heroicon-o-paper-airplane')
                ->color(fn (Invoice $record): string => $record->wasEmailedToCustomer() ? 'warning' : 'success')
                ->visible(fn (Invoice $record) => in_array($record->status, ['draft', 'sent', 'overdue'], true))
                ->requiresConfirmation()
                ->modalHeading(fn (Invoice $record): string => InvoiceEmailUi::modalHeading($record->wasEmailedToCustomer()))
                ->modalDescription(fn (Invoice $record): string => InvoiceEmailUi::confirmMessage($record->user?->email ?? 'the customer', $record->wasEmailedToCustomer()))
                ->modalSubmitActionLabel(fn (Invoice $record): string => InvoiceEmailUi::actionLabel($record->wasEmailedToCustomer()))
                ->action(function (): void {
                    $wasResend = $this->record->wasEmailedToCustomer();

                    try {
                        app(InvoiceService::class)->sendToCustomer($this->record);
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->danger()
                            ->title('Email failed')
                            ->body($e->getMessage())
                            ->duration(12000)
                            ->send();

                        return;
                    }

                    $sent = $this->record->fresh();

                    Notification::make()
                        ->success()
                        ->title(InvoiceEmailUi::successTitle($wasResend))
                        ->body(InvoiceEmailUi::successBody(
                            $sent->invoice_number,
                            $sent->user?->email ?? '',
                            $sent->wantsStripePayment() && ! $sent->isPaid(),
                        ))
                        ->duration(12000)
                        ->send();
                }),
            Actions\EditAction::make(),
        ];
    }
}
