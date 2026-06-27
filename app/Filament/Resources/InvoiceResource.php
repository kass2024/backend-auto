<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\RestrictsStaffAccess;
use App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Support\InvoiceFormSchema;
use App\Filament\Support\Money;
use App\Models\Invoice;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['user', 'vehicle']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema(InvoiceFormSchema::schema());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Invoice #')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer / Client')
                    ->description(fn (Invoice $record): string => collect([
                        $record->user?->email,
                        $record->user?->phone,
                    ])->filter()->implode(' · ') ?: '—')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('user', function (Builder $userQuery) use ($search): void {
                            $userQuery
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('vehicle.plate_number')
                    ->label('Vehicle')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('total')
                    ->formatStateUsing(fn ($state) => Money::format($state))
                    ->sortable(),
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
                Tables\Columns\TextColumn::make('next_service_at')
                    ->label('Next service')
                    ->formatStateUsing(fn ($state, Invoice $record): ?string => $record->nextServiceAtLocal()?->format('M j, Y g:i A'))
                    ->placeholder('—')
                    ->description(fn (Invoice $record): ?string => $record->hasServiceReminder()
                        ? collect([
                            \App\Services\InvoiceServiceReminderService::timezoneLabel($record->serviceReminderTimezone()),
                            match ($record->next_service_reminder_unit) {
                                'minutes' => '5 min before',
                                'hours' => '1 hr before',
                                'days' => '5 days before + on date',
                                default => null,
                            },
                            $record->next_service_repeat && $record->next_service_repeat !== 'none'
                                ? \App\Services\InvoiceServiceReminderService::repeatLabel($record->next_service_repeat)
                                : null,
                        ])->filter()->implode(' · ')
                        : null)
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('paid_at')->dateTime()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
                Tables\Columns\ViewColumn::make('actions')
                    ->label('Actions')
                    ->view('filament.tables.columns.invoice-actions'),
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
            ->actions([])
            ->bulkActions([]);
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
