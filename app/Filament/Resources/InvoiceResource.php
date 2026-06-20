<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\RestrictsStaffAccess;
use App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Resources\InvoiceResource\RelationManagers;
use App\Filament\Support\Money;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Filament\Forms;
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
        return $form->schema([
            Forms\Components\Section::make('Invoice Details')->schema([
                Forms\Components\TextInput::make('invoice_number')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->default(fn () => app(InvoiceService::class)->generateNumber())
                    ->disabled(fn (?Invoice $record) => $record !== null),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name', fn ($query) => $query->where('role', 'customer'))
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('job_card_id')
                    ->relationship('jobCard', 'job_number')
                    ->searchable()
                    ->nullable(),
                Forms\Components\Select::make('status')->options([
                    'draft' => 'Draft',
                    'sent' => 'Sent',
                    'paid' => 'Paid',
                    'overdue' => 'Overdue',
                    'cancelled' => 'Cancelled',
                ])->required()->default('draft'),
                Forms\Components\DatePicker::make('due_date')->default(now()->addDays(14)),
                Forms\Components\Select::make('payment_method')->options([
                    'cash' => 'Cash',
                    'bank_transfer' => 'Bank Transfer',
                    'credit_card' => 'Credit Card',
                    'mobile_money' => 'Mobile Money',
                ])->nullable(),
                Forms\Components\DateTimePicker::make('paid_at')->nullable(),
            ])->columns(2),
            Forms\Components\Section::make('Totals')->schema([
                Forms\Components\TextInput::make('tax_rate')->numeric()->suffix('%')->default(0)->live(onBlur: true),
                Forms\Components\TextInput::make('discount')->numeric()->prefix('$')->default(0),
                Forms\Components\TextInput::make('subtotal')->numeric()->prefix('$')->disabled()->dehydrated(),
                Forms\Components\TextInput::make('tax_amount')->numeric()->prefix('$')->disabled()->dehydrated(),
                Forms\Components\TextInput::make('total')->numeric()->prefix('$')->disabled()->dehydrated(),
            ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label('Customer')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('total')->formatStateUsing(fn ($state) => Money::format($state))->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    'draft' => 'gray',
                    'sent' => 'info',
                    'paid' => 'success',
                    'overdue' => 'danger',
                    'cancelled' => 'warning',
                    default => 'gray',
                }),
                Tables\Columns\TextColumn::make('due_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'draft' => 'Draft',
                    'sent' => 'Sent',
                    'paid' => 'Paid',
                    'overdue' => 'Overdue',
                ]),
            ])
            ->actions([
                Tables\Actions\Action::make('send')
                    ->label('Email Customer')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Send invoice by email')
                    ->modalDescription(fn (Invoice $record) => 'Email invoice to '.$record->user?->email.' via SMTP?')
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
                    ->action(fn (Invoice $record) => $record->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                    ])),
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
        return [
            RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
