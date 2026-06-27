<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\RestrictsStaffAccess;
use App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Support\InvoiceFormSchema;
use App\Filament\Support\Money;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InvoiceResource extends Resource
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

    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'CRM';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema(InvoiceFormSchema::schema());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label('Customer')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('vehicle.plate_number')
                    ->label('Vehicle')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('total')->formatStateUsing(fn ($state) => Money::format($state))->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'paid' => 'Paid',
                        'sent' => 'Unpaid',
                        'overdue' => 'Overdue',
                        'draft' => 'Draft',
                        'cancelled' => 'Cancelled',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'sent' => 'warning',
                        'paid' => 'success',
                        'overdue' => 'danger',
                        'cancelled' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('due_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('paid_at')->dateTime()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'draft' => 'Draft',
                    'sent' => 'Unpaid (sent)',
                    'paid' => 'Paid',
                    'overdue' => 'Overdue',
                ]),
                Tables\Filters\Filter::make('unpaid')
                    ->label('Unpaid only')
                    ->query(fn ($query) => $query->whereIn('status', ['draft', 'sent', 'overdue'])),
                Tables\Filters\Filter::make('paid')
                    ->label('Paid only')
                    ->query(fn ($query) => $query->where('status', 'paid')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('print')
                    ->label('Print')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->url(fn (Invoice $record) => route('filament.admin.invoice.print', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('send')
                    ->label('Email Customer')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Send invoice by email')
                    ->modalDescription(fn (Invoice $record) => 'Email invoice with Stripe payment link to '.$record->user?->email.'?')
                    ->visible(fn (Invoice $record) => in_array($record->status, ['draft', 'sent', 'overdue'], true))
                    ->action(function (Invoice $record) {
                        try {
                            app(InvoiceService::class)->sendToCustomer($record);
                            Notification::make()->title('Invoice emailed successfully')->success()->send();
                        } catch (\Throwable $e) {
                            Notification::make()->title('Email failed')->body($e->getMessage())->danger()->send();
                        }
                    }),
                Tables\Actions\Action::make('markPaid')
                    ->label('Mark Paid')
                    ->icon('heroicon-o-check-circle')
                    ->color('primary')
                    ->visible(fn (Invoice $record) => $record->status !== 'paid')
                    ->requiresConfirmation()
                    ->action(fn (Invoice $record) => app(InvoiceService::class)->markPaid($record)),
                Tables\Actions\Action::make('markUnpaid')
                    ->label('Mark Unpaid')
                    ->icon('heroicon-o-x-circle')
                    ->color('warning')
                    ->visible(fn (Invoice $record) => $record->status === 'paid')
                    ->requiresConfirmation()
                    ->action(fn (Invoice $record) => app(InvoiceService::class)->markUnpaid($record)),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'view' => Pages\ViewInvoice::route('/{record}'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
