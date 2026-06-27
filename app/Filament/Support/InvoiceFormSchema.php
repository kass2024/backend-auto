<?php

namespace App\Filament\Support;

use App\Models\Part;
use App\Models\Service;
use App\Models\Vehicle;
use App\Services\InvoiceService;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;

class InvoiceFormSchema
{
    public static function schema(): array
    {
        return [
            Forms\Components\Section::make('Invoice header')
                ->schema([
                    Forms\Components\TextInput::make('invoice_number')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->default(fn () => app(InvoiceService::class)->generateNumber())
                        ->disabled(fn ($record) => $record !== null),
                    Forms\Components\Select::make('status')
                        ->options([
                            'draft' => 'Draft (unpaid)',
                            'sent' => 'Sent (unpaid)',
                            'paid' => 'Paid',
                            'overdue' => 'Overdue (unpaid)',
                            'cancelled' => 'Cancelled',
                        ])
                        ->required()
                        ->default('sent')
                        ->live(),
                    Forms\Components\DatePicker::make('due_date')->default(now()->addDays(14))->required(),
                    Forms\Components\Toggle::make('send_to_customer')
                        ->label('Email invoice to customer on save')
                        ->default(true)
                        ->helperText('Sends a professional invoice email with Stripe payment link when enabled.')
                        ->visible(fn (string $operation): bool => $operation === 'create'),
                ])
                ->columns(2),

            Forms\Components\Section::make('Customer & vehicle')
                ->description('Select a customer — their vehicle details fill in automatically.')
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->label('Customer')
                        ->relationship('user', 'name', fn ($query) => $query->where('role', 'customer'))
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn ($state, Set $set) => CustomerVehicleAutofill::applyCustomer($set, $state)),
                    Forms\Components\Select::make('vehicle_id')
                        ->label('Vehicle')
                        ->relationship(
                            name: 'vehicle',
                            titleAttribute: 'plate_number',
                            modifyQueryUsing: fn ($query, Get $get) => $query
                                ->when(
                                    filled($get('user_id')),
                                    fn ($query) => $query->where('user_id', (int) $get('user_id')),
                                    fn ($query) => $query->whereRaw('1 = 0'),
                                )
                                ->orderBy('plate_number'),
                        )
                        ->getOptionLabelFromRecordUsing(fn (Vehicle $record): string => CustomerVehicleAutofill::vehicleLabel($record))
                        ->preload()
                        ->live()
                        ->required(fn (Get $get) => CustomerVehicleAutofill::vehicleCount($get) > 0)
                        ->disabled(fn (Get $get) => ! filled($get('user_id')))
                        ->key(fn (Get $get): string => 'invoice-vehicle-'.($get('user_id') ?? 'none'))
                        ->afterStateUpdated(fn ($state, Set $set) => CustomerVehicleAutofill::applyVehicle($set, $state))
                        ->helperText(fn (Get $get): ?string => match (CustomerVehicleAutofill::vehicleCount($get)) {
                            0 => 'This customer has no vehicles on file — add them under Customers first.',
                            1 => 'Vehicle auto-selected from customer profile.',
                            default => 'Customer has multiple vehicles — pick the one for this invoice.',
                        }),
                    Forms\Components\Placeholder::make('vehicle_summary')
                        ->label('Vehicle details')
                        ->content(fn (Get $get) => CustomerVehicleAutofill::vehicleSummary(
                            filled($get('vehicle_id')) ? (int) $get('vehicle_id') : null
                        ))
                        ->columnSpanFull(),
                    Forms\Components\DateTimePicker::make('time_received')->label('Time received'),
                    Forms\Components\DateTimePicker::make('time_promised')->label('Time promised'),
                    Forms\Components\TextInput::make('odometer')
                        ->label('Odometer')
                        ->numeric()
                        ->integer()
                        ->minValue(0),
                ])
                ->columns(2),

            Forms\Components\Section::make('Parts used')
                ->description('Qty, part number and description — matches the parts section on your invoice template.')
                ->schema([
                    Forms\Components\Repeater::make('part_lines')
                        ->label('')
                        ->schema(self::partLineSchema())
                        ->columns(6)
                        ->defaultItems(0)
                        ->addActionLabel('Add part')
                        ->collapsible()
                        ->itemLabel(fn (?array $state): ?string => $state['part_number'] ?? $state['description'] ?? 'Part line')
                        ->live()
                        ->columnSpanFull(),
                    Forms\Components\Placeholder::make('parts_subtotal_preview')
                        ->label('Total parts')
                        ->content(fn (Get $get) => Money::format(self::sumLines($get('part_lines') ?? []))),
                ]),

            Forms\Components\Section::make('Services & labor')
                ->description('Description of work performed and labor charges.')
                ->schema([
                    Forms\Components\Repeater::make('service_lines')
                        ->label('')
                        ->schema(self::serviceLineSchema())
                        ->columns(6)
                        ->defaultItems(1)
                        ->addActionLabel('Add service / labor line')
                        ->collapsible()
                        ->itemLabel(fn (?array $state): ?string => $state['description'] ?? 'Service line')
                        ->live()
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('work_description')
                        ->label('Description of work')
                        ->rows(4)
                        ->columnSpanFull(),
                    Forms\Components\Placeholder::make('labor_subtotal_preview')
                        ->label('Total labor / services')
                        ->content(fn (Get $get) => Money::format(self::sumLines($get('service_lines') ?? []))),
                ]),

            Forms\Components\Section::make('Totals & payment')
                ->schema([
                    Forms\Components\TextInput::make('tax_rate')
                        ->numeric()
                        ->suffix('%')
                        ->default(0)
                        ->live(),
                    Forms\Components\TextInput::make('discount')
                        ->numeric()
                        ->prefix('$')
                        ->default(0)
                        ->live(),
                    Forms\Components\Placeholder::make('parts_total_display')
                        ->label('Parts total')
                        ->content(fn (Get $get): string => Money::format(self::sumLines($get('part_lines') ?? []))),
                    Forms\Components\Placeholder::make('labor_total_display')
                        ->label('Labor total')
                        ->content(fn (Get $get): string => Money::format(self::sumLines($get('service_lines') ?? []))),
                    Forms\Components\Placeholder::make('subtotal_display')
                        ->label('Subtotal')
                        ->content(fn (Get $get): string => Money::format(self::computeTotalsFromState($get)['subtotal'])),
                    Forms\Components\Placeholder::make('tax_amount_display')
                        ->label('Tax amount')
                        ->content(fn (Get $get): string => Money::format(self::computeTotalsFromState($get)['tax_amount'])),
                    Forms\Components\Placeholder::make('total_display')
                        ->label('Total')
                        ->content(fn (Get $get): string => Money::format(self::computeTotalsFromState($get)['total']))
                        ->extraAttributes(['class' => 'text-lg font-bold text-primary-400']),
                    Forms\Components\Hidden::make('parts_total')->dehydrated(),
                    Forms\Components\Hidden::make('labor_total')->dehydrated(),
                    Forms\Components\Hidden::make('subtotal')->dehydrated(),
                    Forms\Components\Hidden::make('tax_amount')->dehydrated(),
                    Forms\Components\Hidden::make('total')->dehydrated(),
                    Forms\Components\Select::make('payment_method')
                        ->options([
                            'cash' => 'Cash',
                            'bank_transfer' => 'Bank Transfer',
                            'credit_card' => 'Credit Card (Stripe)',
                            'mobile_money' => 'Mobile Money',
                        ])
                        ->nullable()
                        ->visible(fn (Get $get) => $get('status') === 'paid'),
                    Forms\Components\DateTimePicker::make('paid_at')
                        ->visible(fn (Get $get) => $get('status') === 'paid'),
                ])
                ->columns(3),
        ];
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    private static function partLineSchema(): array
    {
        return [
            Forms\Components\Select::make('part_id')
                ->label('Inventory part')
                ->searchable()
                ->options(fn (): array => Part::query()
                    ->where('is_active', true)
                    ->orderBy('part_no')
                    ->get()
                    ->mapWithKeys(fn (Part $part) => [
                        $part->id => trim(($part->part_no ?? $part->sku).' — '.$part->name),
                    ])
                    ->all())
                ->live()
                ->afterStateUpdated(function ($state, Set $set, Get $get): void {
                    if (! $state) {
                        return;
                    }

                    $part = Part::find($state);

                    if (! $part) {
                        return;
                    }

                    $set('part_number', $part->part_no ?? $part->sku);
                    $set('description', $part->name);
                    $set('unit_price', $part->unit_price);
                    $set('quantity', 1);
                    self::updateLineTotal($set, $get);
                })
                ->columnSpan(2),
            Forms\Components\TextInput::make('part_number')
                ->label('Part no.')
                ->maxLength(50)
                ->columnSpan(1),
            Forms\Components\TextInput::make('description')
                ->required()
                ->columnSpan(2),
            Forms\Components\TextInput::make('quantity')
                ->numeric()
                ->default(1)
                ->minValue(1)
                ->required()
                ->live()
                ->afterStateUpdated(fn ($state, Set $set, Get $get) => self::updateLineTotal($set, $get))
                ->columnSpan(1),
            Forms\Components\TextInput::make('unit_price')
                ->numeric()
                ->prefix('$')
                ->required()
                ->live()
                ->afterStateUpdated(fn ($state, Set $set, Get $get) => self::updateLineTotal($set, $get))
                ->columnSpan(1),
            Forms\Components\TextInput::make('total')
                ->numeric()
                ->prefix('$')
                ->disabled()
                ->dehydrated()
                ->columnSpan(1),
        ];
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    private static function serviceLineSchema(): array
    {
        return [
            Forms\Components\Select::make('service_id')
                ->label('Service catalog')
                ->searchable()
                ->options(fn (): array => Service::query()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->pluck('name', 'id')
                    ->all())
                ->live()
                ->afterStateUpdated(function ($state, Set $set, Get $get): void {
                    if (! $state) {
                        return;
                    }

                    $service = Service::find($state);

                    if (! $service) {
                        return;
                    }

                    $set('description', $service->name);
                    $set('unit_price', $service->price_from);
                    $set('quantity', 1);
                    self::updateLineTotal($set, $get);
                })
                ->columnSpan(2),
            Forms\Components\TextInput::make('description')
                ->label('Work description')
                ->required()
                ->columnSpan(2),
            Forms\Components\TextInput::make('quantity')
                ->numeric()
                ->default(1)
                ->minValue(1)
                ->required()
                ->live()
                ->afterStateUpdated(fn ($state, Set $set, Get $get) => self::updateLineTotal($set, $get))
                ->columnSpan(1),
            Forms\Components\TextInput::make('unit_price')
                ->label('Rate')
                ->numeric()
                ->prefix('$')
                ->required()
                ->live()
                ->afterStateUpdated(fn ($state, Set $set, Get $get) => self::updateLineTotal($set, $get))
                ->columnSpan(1),
            Forms\Components\TextInput::make('total')
                ->numeric()
                ->prefix('$')
                ->disabled()
                ->dehydrated()
                ->columnSpan(1),
        ];
    }

    private static function updateLineTotal(Set $set, Get $get): void
    {
        $set('total', round((float) $get('quantity') * (float) $get('unit_price'), 2));
    }

    /**
     * @return array{parts_total: float, labor_total: float, subtotal: float, tax_amount: float, total: float}
     */
    public static function computeTotalsFromState(Get $get): array
    {
        $partsTotal = self::sumLines($get('part_lines') ?? []);
        $laborTotal = self::sumLines($get('service_lines') ?? []);
        $subtotal = round($partsTotal + $laborTotal, 2);
        $taxRate = (float) ($get('tax_rate') ?? 0);
        $discount = (float) ($get('discount') ?? 0);
        $taxAmount = round($subtotal * ($taxRate / 100), 2);
        $total = max(0, round($subtotal + $taxAmount - $discount, 2));

        return [
            'parts_total' => $partsTotal,
            'labor_total' => $laborTotal,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total' => $total,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $lines
     */
    public static function sumLines(array $lines): float
    {
        return round(collect($lines)->sum(fn ($line) => (float) ($line['total'] ?? 0)), 2);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function applyComputedTotals(array $data): array
    {
        $partsTotal = self::sumLines($data['part_lines'] ?? []);
        $laborTotal = self::sumLines($data['service_lines'] ?? []);
        $subtotal = round($partsTotal + $laborTotal, 2);
        $taxRate = (float) ($data['tax_rate'] ?? 0);
        $discount = (float) ($data['discount'] ?? 0);
        $taxAmount = round($subtotal * ($taxRate / 100), 2);
        $total = max(0, round($subtotal + $taxAmount - $discount, 2));

        $data['parts_total'] = $partsTotal;
        $data['labor_total'] = $laborTotal;
        $data['subtotal'] = $subtotal;
        $data['tax_amount'] = $taxAmount;
        $data['total'] = $total;

        if (($data['status'] ?? '') === 'paid' && empty($data['paid_at'])) {
            $data['paid_at'] = now();
        }

        return $data;
    }
}
