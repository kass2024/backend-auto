<?php

namespace App\Filament\Support;

use App\Models\Part;
use App\Models\Service;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\QuotationService;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class QuotationFormSchema
{
    public static function schema(): array
    {
        return [
            Forms\Components\Section::make('Quote header')
                ->schema([
                    Forms\Components\TextInput::make('quote_number')
                        ->label('Quote #')
                        ->default(fn () => app(QuotationService::class)->generateNumber())
                        ->disabled(fn ($record) => $record !== null)
                        ->dehydrated()
                        ->required(),
                    Forms\Components\DatePicker::make('issued_at')
                        ->label('Date issued')
                        ->default(now())
                        ->required(),
                    Forms\Components\DatePicker::make('expires_at')
                        ->label('Expiration date')
                        ->default(now()->addDays(14))
                        ->required(),
                    Forms\Components\Toggle::make('send_to_customer')
                        ->label('Email quote to customer on save')
                        ->default(true)
                        ->visible(fn (string $operation): bool => $operation === 'create'),
                ])
                ->columns(2),

            Forms\Components\Section::make('Customer')
                ->description('Search an existing customer, or use + to register a new lead inline.')
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->label('Customer')
                        ->relationship(
                            name: 'user',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn ($query) => $query->where('role', 'customer')->orderBy('name'),
                        )
                        ->getOptionLabelFromRecordUsing(fn (User $record) => "{$record->name} · {$record->email}")
                        ->searchable(['name', 'email', 'phone'])
                        ->preload()
                        ->live()
                        ->afterStateUpdated(function ($state, Set $set): void {
                            $set('vehicle_id', null);
                            if (! $state) {
                                return;
                            }
                            $user = User::find($state);
                            if (! $user) {
                                return;
                            }
                            $set('customer_name', $user->name);
                            $set('customer_email', $user->email);
                            $set('customer_phone', $user->phone);
                        })
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')
                                ->label('Full name')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('email')
                                ->email()
                                ->required()
                                ->unique(User::class, 'email')
                                ->maxLength(255),
                            Forms\Components\TextInput::make('phone')
                                ->tel()
                                ->maxLength(50),
                            Forms\Components\Textarea::make('address')
                                ->rows(2)
                                ->columnSpanFull(),
                        ])
                        ->createOptionUsing(function (array $data): int {
                            return User::create([
                                'name' => $data['name'],
                                'email' => $data['email'],
                                'phone' => $data['phone'] ?? null,
                                'address' => $data['address'] ?? null,
                                'role' => 'customer',
                                'password' => Hash::make(Str::password(12)),
                            ])->getKey();
                        })
                        ->createOptionAction(fn (Forms\Components\Actions\Action $action) => $action
                            ->label('New customer')
                            ->modalHeading('Register new customer')
                            ->modalSubmitActionLabel('Save customer')
                            ->icon('heroicon-o-user-plus'))
                        ->helperText('Click + New customer to register a lead, or leave blank and type details below.'),
                    Forms\Components\TextInput::make('customer_name')
                        ->label('Customer name')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('customer_phone')
                        ->label('Phone')
                        ->tel()
                        ->maxLength(50),
                    Forms\Components\TextInput::make('customer_email')
                        ->label('Email')
                        ->email()
                        ->maxLength(255)
                        ->helperText('Required to email the quote link.'),
                    Forms\Components\Actions::make([
                        Forms\Components\Actions\Action::make('registerCustomerFromFields')
                            ->label('Save details as new customer')
                            ->icon('heroicon-o-user-plus')
                            ->color('success')
                            ->visible(fn (Get $get): bool => blank($get('user_id')))
                            ->action(function (Get $get, Set $set): void {
                                $name = trim((string) $get('customer_name'));
                                $email = trim((string) $get('customer_email'));

                                if ($name === '' || $email === '') {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Name and email are required')
                                        ->warning()
                                        ->send();

                                    throw new \Filament\Support\Exceptions\Halt;
                                }

                                if (User::where('email', $email)->exists()) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Email already registered')
                                        ->body('Select that customer from the list instead.')
                                        ->warning()
                                        ->send();

                                    throw new \Filament\Support\Exceptions\Halt;
                                }

                                $user = User::create([
                                    'name' => $name,
                                    'email' => $email,
                                    'phone' => $get('customer_phone') ?: null,
                                    'role' => 'customer',
                                    'password' => Hash::make(Str::password(12)),
                                ]);

                                $set('user_id', $user->id);

                                \Filament\Notifications\Notification::make()
                                    ->title('Customer registered')
                                    ->body($user->name.' can now have vehicles saved.')
                                    ->success()
                                    ->send();
                            }),
                    ])->columnSpanFull(),
                ])
                ->columns(2),

            Forms\Components\Section::make('Vehicle')
                ->description('Type vehicle details below anytime. Optionally save them to a customer with + New vehicle.')
                ->schema([
                    Forms\Components\Select::make('vehicle_id')
                        ->label('Saved vehicle (optional)')
                        ->options(function (Get $get): array {
                            $userId = $get('user_id');
                            if (! $userId) {
                                return [];
                            }

                            return Vehicle::query()
                                ->where('user_id', $userId)
                                ->orderBy('plate_number')
                                ->get()
                                ->mapWithKeys(fn (Vehicle $v) => [
                                    $v->id => trim("{$v->year} {$v->make} {$v->model} — {$v->plate_number}"),
                                ])
                                ->all();
                        })
                        ->searchable()
                        ->live()
                        ->helperText(fn (Get $get): string => filled($get('user_id'))
                            ? 'Pick a saved vehicle, or click + to register a new one.'
                            : 'No customer linked yet — you can still type Make / Model / Year / VIN / Plate below.')
                        ->afterStateUpdated(function ($state, Set $set): void {
                            if (! $state) {
                                return;
                            }
                            $vehicle = Vehicle::find($state);
                            if (! $vehicle) {
                                return;
                            }
                            $set('vehicle_make', $vehicle->make);
                            $set('vehicle_model', $vehicle->model);
                            $set('vehicle_year', $vehicle->year);
                            $set('vehicle_vin', $vehicle->vin);
                            $set('vehicle_plate', $vehicle->plate_number);
                        })
                        ->createOptionForm([
                            Forms\Components\TextInput::make('plate_number')
                                ->label('Plate number')
                                ->required()
                                ->maxLength(20),
                            Forms\Components\TextInput::make('make')->required()->maxLength(100),
                            Forms\Components\TextInput::make('model')->required()->maxLength(100),
                            Forms\Components\TextInput::make('year')
                                ->numeric()
                                ->required()
                                ->minValue(1900)
                                ->maxValue((int) date('Y') + 1),
                            Forms\Components\TextInput::make('vin')->label('VIN')->maxLength(50),
                            Forms\Components\TextInput::make('color')->maxLength(50),
                        ])
                        ->createOptionUsing(function (array $data, Get $get, Set $set): int {
                            $userId = $get('user_id');

                            if (! $userId) {
                                $name = trim((string) $get('customer_name'));
                                $email = trim((string) $get('customer_email'));

                                if ($name === '' || $email === '') {
                                    throw new \RuntimeException('Enter customer name and email first (or select a customer), then add the vehicle.');
                                }

                                $existing = User::where('email', $email)->first();
                                if ($existing) {
                                    $userId = $existing->id;
                                } else {
                                    $userId = User::create([
                                        'name' => $name,
                                        'email' => $email,
                                        'phone' => $get('customer_phone') ?: null,
                                        'role' => 'customer',
                                        'password' => Hash::make(Str::password(12)),
                                    ])->getKey();
                                }

                                $set('user_id', $userId);
                            }

                            $vehicle = Vehicle::create([
                                'user_id' => $userId,
                                'plate_number' => $data['plate_number'],
                                'make' => $data['make'] ?? null,
                                'model' => $data['model'] ?? null,
                                'year' => $data['year'] ?? null,
                                'vin' => $data['vin'] ?? null,
                                'color' => $data['color'] ?? null,
                            ]);

                            $set('vehicle_make', $vehicle->make);
                            $set('vehicle_model', $vehicle->model);
                            $set('vehicle_year', $vehicle->year);
                            $set('vehicle_vin', $vehicle->vin);
                            $set('vehicle_plate', $vehicle->plate_number);

                            return $vehicle->getKey();
                        })
                        ->createOptionAction(fn (Forms\Components\Actions\Action $action) => $action
                            ->label('New vehicle')
                            ->modalHeading('Add vehicle')
                            ->modalSubmitActionLabel('Save vehicle')
                            ->icon('heroicon-o-truck'))
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('vehicle_make')
                        ->label('Make')
                        ->maxLength(100)
                        ->placeholder('e.g. Toyota'),
                    Forms\Components\TextInput::make('vehicle_model')
                        ->label('Model')
                        ->maxLength(100)
                        ->placeholder('e.g. Corolla'),
                    Forms\Components\TextInput::make('vehicle_year')
                        ->label('Year')
                        ->numeric()
                        ->minValue(1900)
                        ->maxValue((int) date('Y') + 1)
                        ->placeholder('YYYY'),
                    Forms\Components\TextInput::make('vehicle_vin')
                        ->label('VIN')
                        ->maxLength(50)
                        ->placeholder('Vehicle identification number'),
                    Forms\Components\TextInput::make('vehicle_plate')
                        ->label('Plate')
                        ->maxLength(30)
                        ->placeholder('License plate'),
                    Forms\Components\Actions::make([
                        Forms\Components\Actions\Action::make('saveVehicleFromFields')
                            ->label('Save these details as new vehicle')
                            ->icon('heroicon-o-truck')
                            ->color('success')
                            ->visible(fn (Get $get): bool => blank($get('vehicle_id')))
                            ->action(function (Get $get, Set $set): void {
                                $plate = trim((string) $get('vehicle_plate'));
                                $make = trim((string) $get('vehicle_make'));
                                $model = trim((string) $get('vehicle_model'));

                                if ($plate === '' || $make === '' || $model === '') {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Plate, make, and model are required')
                                        ->warning()
                                        ->send();

                                    throw new \Filament\Support\Exceptions\Halt;
                                }

                                $userId = $get('user_id');
                                if (! $userId) {
                                    $name = trim((string) $get('customer_name'));
                                    $email = trim((string) $get('customer_email'));

                                    if ($name === '' || $email === '') {
                                        \Filament\Notifications\Notification::make()
                                            ->title('Customer name and email required')
                                            ->body('Fill customer details first so the vehicle can be saved to their account.')
                                            ->warning()
                                            ->send();

                                        throw new \Filament\Support\Exceptions\Halt;
                                    }

                                    $existing = User::where('email', $email)->first();
                                    $userId = $existing
                                        ? $existing->id
                                        : User::create([
                                            'name' => $name,
                                            'email' => $email,
                                            'phone' => $get('customer_phone') ?: null,
                                            'role' => 'customer',
                                            'password' => Hash::make(Str::password(12)),
                                        ])->getKey();

                                    $set('user_id', $userId);
                                }

                                if (Vehicle::where('plate_number', $plate)->exists()) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Plate already exists')
                                        ->body('Select that vehicle from the list instead.')
                                        ->warning()
                                        ->send();

                                    throw new \Filament\Support\Exceptions\Halt;
                                }

                                $vehicle = Vehicle::create([
                                    'user_id' => $userId,
                                    'plate_number' => $plate,
                                    'make' => $make,
                                    'model' => $model,
                                    'year' => $get('vehicle_year') ?: null,
                                    'vin' => $get('vehicle_vin') ?: null,
                                ]);

                                $set('vehicle_id', $vehicle->id);

                                \Filament\Notifications\Notification::make()
                                    ->title('Vehicle saved')
                                    ->body($vehicle->display_name.' — '.$vehicle->plate_number)
                                    ->success()
                                    ->send();
                            }),
                    ])->columnSpanFull(),
                ])
                ->columns(2),

            Forms\Components\Section::make('Repair overview')
                ->schema([
                    Forms\Components\Textarea::make('problem_description')
                        ->label('Problem description')
                        ->rows(3)
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('inspection_findings')
                        ->label('Inspection findings')
                        ->rows(3)
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('proposed_repairs')
                        ->label('Proposed repairs')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Parts')
                ->description('Use inventory parts or enter custom lines.')
                ->schema([
                    Forms\Components\Repeater::make('part_lines')
                        ->label('')
                        ->schema(self::partLineSchema())
                        ->columns(6)
                        ->defaultItems(0)
                        ->addActionLabel('Add part')
                        ->collapsible()
                        ->live()
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Labor')
                ->description('Use catalog services or enter labor fees.')
                ->schema([
                    Forms\Components\Repeater::make('labor_lines')
                        ->label('')
                        ->schema(self::laborLineSchema())
                        ->columns(6)
                        ->defaultItems(0)
                        ->addActionLabel('Add labor / service')
                        ->collapsible()
                        ->live()
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Additional costs')
                ->schema([
                    Forms\Components\Repeater::make('additional_lines')
                        ->label('')
                        ->schema(self::additionalLineSchema())
                        ->columns(5)
                        ->defaultItems(0)
                        ->addActionLabel('Add fee')
                        ->collapsible()
                        ->live()
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Totals')
                ->schema([
                    Forms\Components\TextInput::make('tax_rate')->numeric()->suffix('%')->default(0)->live(),
                    Forms\Components\TextInput::make('discount')->numeric()->prefix('$')->default(0)->live(),
                    Forms\Components\Placeholder::make('parts_preview')
                        ->label('Parts')
                        ->content(fn (Get $get) => Money::format(self::sumLines($get('part_lines') ?? []))),
                    Forms\Components\Placeholder::make('labor_preview')
                        ->label('Labor')
                        ->content(fn (Get $get) => Money::format(self::sumLines($get('labor_lines') ?? []))),
                    Forms\Components\Placeholder::make('additional_preview')
                        ->label('Additional')
                        ->content(fn (Get $get) => Money::format(self::sumLines($get('additional_lines') ?? []))),
                    Forms\Components\Placeholder::make('total_preview')
                        ->label('Total due')
                        ->content(fn (Get $get) => Money::format(self::computeTotal($get)))
                        ->extraAttributes(['class' => 'text-lg font-bold text-primary-400']),
                ])
                ->columns(3),

            Forms\Components\Section::make('Terms')
                ->collapsed()
                ->schema([
                    Forms\Components\Textarea::make('payment_terms')
                        ->label('Payment terms')
                        ->rows(2)
                        ->default(fn () => \App\Models\Quotation::defaultPaymentTerms())
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('warranty_terms')
                        ->label('Warranty')
                        ->rows(2)
                        ->default(fn () => \App\Models\Quotation::defaultWarrantyTerms())
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('authorization_terms')
                        ->label('Authorization')
                        ->rows(2)
                        ->default(fn () => \App\Models\Quotation::defaultAuthorizationTerms())
                        ->columnSpanFull(),
                ]),
        ];
    }

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
                    $set('description', $part->name ?: ($part->part_no ?: 'Part'));
                    $set('unit_price', $part->unit_price);
                    $set('quantity', 1);
                    self::updateLineTotal($set, $get);
                })
                ->columnSpan(2),
            Forms\Components\TextInput::make('description')->label('Part description')->required()->columnSpan(2),
            Forms\Components\TextInput::make('quantity')->numeric()->default(1)->minValue(0.01)->required()->live()
                ->afterStateUpdated(fn ($state, Set $set, Get $get) => self::updateLineTotal($set, $get)),
            Forms\Components\TextInput::make('unit_price')->label('Unit price')->numeric()->prefix('$')->required()->live()
                ->afterStateUpdated(fn ($state, Set $set, Get $get) => self::updateLineTotal($set, $get)),
            Forms\Components\TextInput::make('total')->numeric()->prefix('$')->disabled()->dehydrated(),
        ];
    }

    private static function laborLineSchema(): array
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
            Forms\Components\TextInput::make('description')->label('Labor description')->required()->columnSpan(2),
            Forms\Components\TextInput::make('quantity')->label('Hours/qty')->numeric()->default(1)->minValue(0.01)->required()->live()
                ->afterStateUpdated(fn ($state, Set $set, Get $get) => self::updateLineTotal($set, $get)),
            Forms\Components\TextInput::make('unit_price')->label('Rate')->numeric()->prefix('$')->required()->live()
                ->afterStateUpdated(fn ($state, Set $set, Get $get) => self::updateLineTotal($set, $get)),
            Forms\Components\TextInput::make('total')->numeric()->prefix('$')->disabled()->dehydrated(),
        ];
    }

    private static function additionalLineSchema(): array
    {
        return [
            Forms\Components\TextInput::make('description')->label('Cost description')->required()->columnSpan(2),
            Forms\Components\TextInput::make('quantity')->numeric()->default(1)->minValue(0.01)->required()->live()
                ->afterStateUpdated(fn ($state, Set $set, Get $get) => self::updateLineTotal($set, $get)),
            Forms\Components\TextInput::make('unit_price')->label('Unit price')->numeric()->prefix('$')->required()->live()
                ->afterStateUpdated(fn ($state, Set $set, Get $get) => self::updateLineTotal($set, $get)),
            Forms\Components\TextInput::make('total')->numeric()->prefix('$')->disabled()->dehydrated(),
        ];
    }

    private static function updateLineTotal(Set $set, Get $get): void
    {
        $set('total', round((float) $get('quantity') * (float) $get('unit_price'), 2));
    }

    /** @param  array<int, array<string, mixed>>  $lines */
    public static function sumLines(array $lines): float
    {
        return round(collect($lines)->sum(fn ($line) => (float) ($line['total'] ?? 0)), 2);
    }

    public static function computeTotal(Get $get): float
    {
        $subtotal = self::sumLines($get('part_lines') ?? [])
            + self::sumLines($get('labor_lines') ?? [])
            + self::sumLines($get('additional_lines') ?? []);
        $discount = (float) ($get('discount') ?? 0);
        $tax = round(max(0, $subtotal - $discount) * ((float) ($get('tax_rate') ?? 0) / 100), 2);

        return max(0, round($subtotal - $discount + $tax, 2));
    }
}
