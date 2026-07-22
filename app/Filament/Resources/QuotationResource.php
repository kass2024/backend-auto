<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\RestrictsStaffAccess;
use App\Filament\Resources\QuotationResource\Pages;
use App\Filament\Support\Money;
use App\Filament\Support\QuotationFormSchema;
use App\Models\Quotation;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class QuotationResource extends Resource
{
    use RestrictsStaffAccess;

    protected static function staffNavigation(): bool
    {
        return true;
    }

    protected static function staffFullAccess(): bool
    {
        return true;
    }

    protected static ?string $model = Quotation::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'Smart Quotes';

    protected static ?string $modelLabel = 'Smart Quote';

    protected static ?string $pluralModelLabel = 'Smart Quotes';

    protected static ?string $navigationGroup = 'CRM';

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['user', 'vehicle', 'invoice']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema(QuotationFormSchema::schema());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('quote_number')
                    ->label('Quote #')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Customer')
                    ->description(fn (Quotation $record): string => collect([
                        $record->customer_email,
                        $record->customer_phone,
                    ])->filter()->implode(' · ') ?: '—')
                    ->searchable(['customer_name', 'customer_email', 'customer_phone'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('vehicle_summary')
                    ->label('Vehicle')
                    ->state(fn (Quotation $record): string => trim(collect([
                        $record->vehicle_year,
                        $record->vehicle_make,
                        $record->vehicle_model,
                        $record->vehicle_plate ? '('.$record->vehicle_plate.')' : null,
                    ])->filter()->implode(' ')) ?: '—'),
                Tables\Columns\TextColumn::make('total')
                    ->formatStateUsing(fn ($state) => Money::format($state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (Quotation $record): string => $record->statusLabel())
                    ->color(fn (string $state): string => match ($state) {
                        'accepted', 'converted' => 'success',
                        'sent', 'viewed' => 'warning',
                        'declined', 'expired' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expires')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('signed_at')
                    ->label('E-signed')
                    ->boolean()
                    ->getStateUsing(fn (Quotation $record): bool => filled($record->signed_at))
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\Action::make('printPdf')
                    ->label('Print')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->url(fn (Quotation $record): string => route('filament.admin.quotations.print', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('copyLink')
                    ->label('Copy link')
                    ->icon('heroicon-o-link')
                    ->color('gray')
                    ->action(function (Quotation $record): void {
                        $url = $record->publicUrl();
                        \Filament\Notifications\Notification::make()
                            ->title('Customer quote link')
                            ->body($url)
                            ->success()
                            ->persistent()
                            ->send();
                    }),
                Tables\Actions\Action::make('email')
                    ->label('Email')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Email quote to customer')
                    ->action(function (Quotation $record): void {
                        try {
                            app(\App\Services\QuotationService::class)->sendToCustomer($record);
                            \Filament\Notifications\Notification::make()
                                ->title('Quote emailing')
                                ->body('Sending to '.($record->customer_email ?: $record->user?->email).'.')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Email failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn (Quotation $record): bool => ! in_array($record->status, ['accepted', 'converted'], true)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuotations::route('/'),
            'create' => Pages\CreateQuotation::route('/create'),
            'view' => Pages\ViewQuotation::route('/{record}'),
            'edit' => Pages\EditQuotation::route('/{record}/edit'),
        ];
    }
}
