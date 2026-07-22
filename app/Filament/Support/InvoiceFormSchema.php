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
                        ->live()
                        ->visible(fn (string $operation): bool => $operation !== 'create')
                        ->helperText('New invoices are created unpaid and emailed as unpaid. Mark paid after the customer pays.'),
                    Forms\Components\DatePicker::make('due_date')->default(now()->addDays(14))->required(),
                    Forms\Components\Toggle::make('send_to_customer')
                        ->label('Email invoice to customer on save')
                        ->default(true)
                        ->helperText('Sends the branded unpaid invoice email automatically. Stripe link is included when Credit Card (Stripe) is selected.')
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
                ->description('Qty, part no. and part name — matches the parts section on your invoice template.')
                ->schema([
                    Forms\Components\Repeater::make('part_lines')
                        ->label('')
                        ->schema(self::partLineSchema())
                        ->columns(6)
                        ->defaultItems(0)
                        ->addActionLabel('Add part')
                        ->collapsible()
                        ->itemLabel(fn (?array $state): ?string => $state['description'] ?? $state['part_number'] ?? 'Part line')
                        ->live()
                        ->columnSpanFull(),
                    Forms\Components\Placeholder::make('parts_subtotal_preview')
                        ->label('Total parts')
                        ->content(fn (Get $get) => Money::format(self::sumLines($get('part_lines') ?? []))),
                ]),

            Forms\Components\Section::make('Labor & services')
                ->description('Enter the labor fee and any catalog services. Labor appears on the printed invoice.')
                ->schema([
                    Forms\Components\TextInput::make('labor_fee')
                        ->label('Labor fee')
                        ->numeric()
                        ->prefix('$')
                        ->default(0)
                        ->minValue(0)
                        ->live()
                        ->helperText('This amount is shown as Labor on the final printed invoice.')
                        ->columnSpanFull(),
                    Forms\Components\Repeater::make('service_lines')
                        ->label('Additional services')
                        ->schema(self::serviceLineSchema())
                        ->columns(6)
                        ->defaultItems(0)
                        ->addActionLabel('Add service line')
                        ->collapsible()
                        ->itemLabel(fn (?array $state): ?string => $state['description'] ?? 'Service line')
                        ->live()
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('work_description')
                        ->label('Description of work')
                        ->rows(4)
                        ->columnSpanFull(),
                    Forms\Components\Placeholder::make('labor_subtotal_preview')
                        ->label('Labor total')
                        ->content(fn (Get $get) => Money::format(
                            self::sumLines($get('service_lines') ?? []) + (float) ($get('labor_fee') ?? 0)
                        )),
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
                        ->label('Labor')
                        ->content(fn (Get $get): string => Money::format(
                            self::sumLines($get('service_lines') ?? []) + (float) ($get('labor_fee') ?? 0)
                        )),
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
                        ->label('Payment method')
                        ->options([
                            'cash' => 'Cash',
                            'check' => 'Check',
                            'bank_transfer' => 'Bank Transfer',
                            'credit_card' => 'Credit Card (Stripe)',
                            'mobile_money' => 'Mobile Money',
                        ])
                        ->default('cash')
                        ->live()
                        ->helperText(fn (Get $get, string $operation): string => match (true) {
                            $operation === 'create' && $get('payment_method') === 'credit_card' => 'Invoice stays unpaid. A Stripe payment link is included in the customer email.',
                            $operation === 'create' => 'Invoice is created unpaid and emailed as unpaid. Preferred payment method is shown for remittance.',
                            $get('status') === 'paid' => 'How the customer paid — shown on the invoice.',
                            $get('payment_method') === 'credit_card' => 'Customer email will include a Stripe payment link while the invoice is unpaid.',
                            default => 'Choose Credit Card (Stripe) to collect payment online before marking paid.',
                        })
                        ->required(fn (Get $get, string $operation): bool => $operation === 'create' || $get('status') === 'paid'),
                    Forms\Components\DateTimePicker::make('paid_at')
                        ->visible(fn (Get $get, string $operation): bool => $operation !== 'create' && $get('status') === 'paid'),
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
                    $set('description', $part->name ?: ($part->manufacturer_part_number ?: 'Part'));
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
                ->label('Part name')
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
        $laborTotal = round(self::sumLines($get('service_lines') ?? []) + (float) ($get('labor_fee') ?? 0), 2);
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
     * @param  array<string, mixed>|null  $line
     */
    public static function isLaborLine(?array $line): bool
    {
        if (! $line) {
            return false;
        }

        return strcasecmp(trim((string) ($line['description'] ?? '')), 'Labor') === 0
            && blank($line['service_id'] ?? null);
    }

    /**
     * Pull a dedicated Labor fee out of service lines for the form field.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function extractLaborFee(array $data): array
    {
        $laborFee = 0.0;
        $rest = [];

        foreach ($data['service_lines'] ?? [] as $line) {
            if (self::isLaborLine($line)) {
                $laborFee += (float) ($line['total'] ?? $line['unit_price'] ?? 0);
            } else {
                $rest[] = $line;
            }
        }

        $data['service_lines'] = $rest;
        $data['labor_fee'] = round($laborFee, 2);

        return $data;
    }

    /**
     * Persist Labor fee as a service line so it prints on the invoice.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function mergeLaborFeeIntoServiceLines(array $data): array
    {
        $lines = array_values(array_filter(
            $data['service_lines'] ?? [],
            fn ($line) => ! self::isLaborLine(is_array($line) ? $line : null)
        ));

        $fee = round((float) ($data['labor_fee'] ?? 0), 2);

        if ($fee > 0) {
            array_unshift($lines, [
                'service_id' => null,
                'description' => 'Labor',
                'quantity' => 1,
                'unit_price' => $fee,
                'total' => $fee,
            ]);
        }

        $data['service_lines'] = $lines;
        unset($data['labor_fee']);

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function applyComputedTotals(array $data, bool $skipPaymentDefaults = false): array
    {
        $partsTotal = self::sumLines($data['part_lines'] ?? []);
        $laborTotal = round(self::sumLines($data['service_lines'] ?? []) + (float) ($data['labor_fee'] ?? 0), 2);
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

        if ($skipPaymentDefaults) {
            return $data;
        }

        return self::applyPaymentDefaults($data);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function applyPaymentDefaults(array $data, string $operation = 'edit'): array
    {
        if ($operation === 'create') {
            // Always create unpaid so customer emails show amount due + QR pay instructions.
            $data['status'] = 'sent';
            $data['paid_at'] = null;
            $data['payment_method'] = $data['payment_method'] ?? 'cash';

            return $data;
        }

        if (($data['status'] ?? '') === 'paid') {
            if (empty($data['paid_at'])) {
                $data['paid_at'] = now();
            }

            if (empty($data['payment_method'])) {
                $data['payment_method'] = 'cash';
            }
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function applyComputedTotalsForCreate(array $data): array
    {
        return self::applyPaymentDefaults(
            self::applyComputedTotals($data, skipPaymentDefaults: true),
            'create',
        );
    }
}
