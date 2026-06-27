<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Filament\Support\Money;
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
                Infolists\Components\TextEntry::make('total')->formatStateUsing(fn ($state) => Money::format($state)),
            ])->columns(3),
            Infolists\Components\Section::make('Parts used')->schema([
                Infolists\Components\RepeatableEntry::make('partItems')
                    ->label('')
                    ->schema([
                        Infolists\Components\TextEntry::make('part_number')->label('Part no.'),
                        Infolists\Components\TextEntry::make('description'),
                        Infolists\Components\TextEntry::make('quantity'),
                        Infolists\Components\TextEntry::make('total')->formatStateUsing(fn ($state) => Money::format($state)),
                    ])
                    ->columns(4)
                    ->placeholder('No parts listed'),
            ]),
            Infolists\Components\Section::make('Services & labor')->schema([
                Infolists\Components\RepeatableEntry::make('serviceItems')
                    ->label('')
                    ->schema([
                        Infolists\Components\TextEntry::make('description')->columnSpan(2),
                        Infolists\Components\TextEntry::make('quantity'),
                        Infolists\Components\TextEntry::make('total')->formatStateUsing(fn ($state) => Money::format($state)),
                    ])
                    ->columns(4)
                    ->placeholder('No services listed'),
                Infolists\Components\TextEntry::make('work_description')
                    ->label('Description of work')
                    ->placeholder('—')
                    ->columnSpanFull(),
            ]),
            Infolists\Components\Section::make('Totals')->schema([
                Infolists\Components\TextEntry::make('parts_total')->formatStateUsing(fn ($state) => Money::format($state)),
                Infolists\Components\TextEntry::make('labor_total')->formatStateUsing(fn ($state) => Money::format($state)),
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
                ->label('Email Customer')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->visible(fn (Invoice $record) => in_array($record->status, ['draft', 'sent', 'overdue'], true))
                ->requiresConfirmation()
                ->modalHeading('Email invoice to customer')
                ->modalDescription('Email this invoice with payment link to the customer?')
                ->modalSubmitActionLabel('Send email')
                ->action(function (Actions\Action $action): void {
                    app(InvoiceService::class)->sendToCustomer($this->record);
                    $action->success();
                })
                ->failureNotification(
                    Notification::make()
                        ->danger()
                        ->title('Email failed')
                )
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Invoice email sent')
                        ->body('The customer will receive the invoice and payment link shortly.')
                ),
            Actions\EditAction::make(),
        ];
    }
}
