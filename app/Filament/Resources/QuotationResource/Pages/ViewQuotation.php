<?php

namespace App\Filament\Resources\QuotationResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Filament\Resources\QuotationResource;
use App\Filament\Support\Money;
use App\Models\Quotation;
use App\Services\QuotationService;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewQuotation extends ViewRecord
{
    protected static string $resource = QuotationResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Quote')->schema([
                Infolists\Components\TextEntry::make('quote_number')->label('Quote #'),
                Infolists\Components\TextEntry::make('status')
                    ->badge()
                    ->formatStateUsing(fn (Quotation $record): string => $record->statusLabel()),
                Infolists\Components\TextEntry::make('issued_at')->date(),
                Infolists\Components\TextEntry::make('expires_at')->date(),
                Infolists\Components\TextEntry::make('total')->formatStateUsing(fn ($state) => Money::format($state)),
                Infolists\Components\TextEntry::make('signed_at')->dateTime()->placeholder('Not signed yet'),
            ])->columns(3),

            Infolists\Components\Section::make('Customer & vehicle')->schema([
                Infolists\Components\TextEntry::make('customer_name'),
                Infolists\Components\TextEntry::make('customer_phone')->placeholder('—'),
                Infolists\Components\TextEntry::make('customer_email')->placeholder('—'),
                Infolists\Components\TextEntry::make('vehicle_make')->placeholder('—'),
                Infolists\Components\TextEntry::make('vehicle_model')->placeholder('—'),
                Infolists\Components\TextEntry::make('vehicle_year')->placeholder('—'),
                Infolists\Components\TextEntry::make('vehicle_vin')->label('VIN')->placeholder('—'),
                Infolists\Components\TextEntry::make('vehicle_plate')->label('Plate')->placeholder('—'),
            ])->columns(2),

            Infolists\Components\Section::make('Repair overview')->schema([
                Infolists\Components\TextEntry::make('problem_description')->placeholder('—')->columnSpanFull(),
                Infolists\Components\TextEntry::make('inspection_findings')->placeholder('—')->columnSpanFull(),
                Infolists\Components\TextEntry::make('proposed_repairs')->placeholder('—')->columnSpanFull(),
            ]),

            Infolists\Components\Section::make('Line items')->schema([
                Infolists\Components\RepeatableEntry::make('items')
                    ->label('')
                    ->schema([
                        Infolists\Components\TextEntry::make('type')->badge(),
                        Infolists\Components\TextEntry::make('description')->columnSpan(2),
                        Infolists\Components\TextEntry::make('quantity'),
                        Infolists\Components\TextEntry::make('unit_price')->formatStateUsing(fn ($state) => Money::format($state)),
                        Infolists\Components\TextEntry::make('total')->formatStateUsing(fn ($state) => Money::format($state)),
                    ])
                    ->columns(6),
            ]),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('printPdf')
                ->label('Print / PDF')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(fn (Quotation $record): string => route('filament.admin.quotations.print', $record))
                ->openUrlInNewTab(),
            Actions\Action::make('openCustomerLink')
                ->label('Open customer link')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url(fn (Quotation $record): string => $record->publicUrl())
                ->openUrlInNewTab(),
            Actions\Action::make('copyLink')
                ->label('Copy link')
                ->icon('heroicon-o-clipboard-document')
                ->color('gray')
                ->action(function (Quotation $record): void {
                    $url = $record->publicUrl();
                    Notification::make()
                        ->title('Copy this link for the customer')
                        ->body($url)
                        ->success()
                        ->persistent()
                        ->send();
                }),
            Actions\Action::make('email')
                ->label('Email customer')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->requiresConfirmation()
                ->action(function (Quotation $record): void {
                    try {
                        app(QuotationService::class)->sendToCustomer($record);
                        Notification::make()
                            ->title('Quote emailing')
                            ->body('Sending to '.($record->customer_email ?: $record->user?->email).'.')
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Email failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Actions\Action::make('convert')
                ->label('Create invoice')
                ->icon('heroicon-o-document-text')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Create invoice from accepted quote')
                ->modalDescription('Only available after the customer e-signs. Work should start after acceptance.')
                ->visible(fn (Quotation $record): bool => $record->isAccepted() && blank($record->invoice_id))
                ->action(function (Quotation $record): void {
                    try {
                        $invoice = app(QuotationService::class)->convertToInvoice($record);
                        Notification::make()
                            ->title('Invoice created')
                            ->body('Invoice '.$invoice->invoice_number.' created from quote '.$record->quote_number.'.')
                            ->success()
                            ->send();
                        $this->redirect(InvoiceResource::getUrl('view', ['record' => $invoice]));
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Could not create invoice')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Actions\EditAction::make()
                ->visible(fn (Quotation $record): bool => ! in_array($record->status, ['accepted', 'converted'], true)),
        ];
    }
}
