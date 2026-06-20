<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\RestrictsStaffAccess;
use App\Filament\Resources\JobCardResource\Pages;
use App\Filament\Support\Money;
use App\Models\JobCard;
use App\Models\Service;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\InvoiceService;
use App\Services\JobCardWorkflowService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class JobCardResource extends Resource
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

    protected static ?string $model = JobCard::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Job Cards';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Job Details')->schema([
                Forms\Components\TextInput::make('job_number')
                    ->default(fn () => app(JobCardWorkflowService::class)->generateJobNumber())
                    ->disabled()
                    ->dehydrated()
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'waiting' => 'Waiting',
                        'diagnosing' => 'Diagnosing',
                        'parts_ordered' => 'Parts Ordered',
                        'in_progress' => 'In Progress',
                        'quality_check' => 'Quality Check',
                        'ready_for_pickup' => 'Ready for Pickup',
                        'delivered' => 'Delivered',
                    ])
                    ->default('waiting')
                    ->required()
                    ->live(),
                Forms\Components\Select::make('mechanic_id')
                    ->relationship('mechanic', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('booking_id')
                    ->relationship('booking', 'reference')
                    ->searchable()
                    ->preload(),
            ])->columns(2),

            Forms\Components\Section::make('Customer & Vehicle')
                ->description('Search for an existing customer or register a new one. Vehicles are filtered by the selected customer.')
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->label('Customer')
                        ->relationship(
                            name: 'user',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn ($query) => $query->where('role', 'customer')->orderBy('name'),
                        )
                        ->getOptionLabelFromRecordUsing(fn (User $record) => "{$record->name} ({$record->email})")
                        ->searchable(['name', 'email', 'phone'])
                        ->preload()
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn (callable $set) => $set('vehicle_id', null))
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')->required()->maxLength(255),
                            Forms\Components\TextInput::make('email')->email()->required()->maxLength(255),
                            Forms\Components\TextInput::make('phone')->tel()->maxLength(50),
                            Forms\Components\Textarea::make('address')->rows(2)->columnSpanFull(),
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
                        }),

                    Forms\Components\Select::make('vehicle_id')
                        ->label('Vehicle')
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
                                    $v->id => trim("{$v->plate_number} — {$v->year} {$v->make} {$v->model}"),
                                ])
                                ->all();
                        })
                        ->searchable()
                        ->required()
                        ->disabled(fn (Get $get) => ! $get('user_id'))
                        ->helperText(fn (Get $get) => $get('user_id') ? null : 'Select a customer first')
                        ->createOptionForm([
                            Forms\Components\TextInput::make('plate_number')->required()->maxLength(20),
                            Forms\Components\TextInput::make('make')->maxLength(100),
                            Forms\Components\TextInput::make('model')->maxLength(100),
                            Forms\Components\TextInput::make('year')->numeric()->minValue(1900)->maxValue(now()->year + 1),
                            Forms\Components\TextInput::make('color')->maxLength(50),
                            Forms\Components\TextInput::make('vin')->maxLength(50),
                        ])
                        ->createOptionUsing(function (array $data, Get $get): int {
                            return Vehicle::create([
                                'user_id' => $get('user_id'),
                                'plate_number' => $data['plate_number'],
                                'make' => $data['make'] ?? null,
                                'model' => $data['model'] ?? null,
                                'year' => $data['year'] ?? null,
                                'color' => $data['color'] ?? null,
                                'vin' => $data['vin'] ?? null,
                            ])->getKey();
                        }),
                ])->columns(2),

            Forms\Components\Section::make('Services')
                ->description('Link services from your catalog. Prices auto-fill from the service catalog.')
                ->schema([
                    Forms\Components\Repeater::make('line_items')
                        ->label('')
                        ->schema([
                            Forms\Components\Select::make('service_id')
                                ->label('Service')
                                ->options(fn () => Service::query()
                                    ->where('is_active', true)
                                    ->orderBy('sort_order')
                                    ->pluck('name', 'id'))
                                ->searchable()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set): void {
                                    if (! $state) {
                                        return;
                                    }
                                    $service = Service::find($state);
                                    if ($service) {
                                        $set('description', $service->name);
                                        $set('unit_price', $service->price_from);
                                    }
                                }),
                            Forms\Components\TextInput::make('description')
                                ->required()
                                ->maxLength(255)
                                ->columnSpan(2),
                            Forms\Components\TextInput::make('quantity')
                                ->numeric()
                                ->default(1)
                                ->minValue(1)
                                ->required(),
                            Forms\Components\TextInput::make('unit_price')
                                ->numeric()
                                ->prefix('$')
                                ->required(),
                        ])
                        ->columns(5)
                        ->defaultItems(1)
                        ->addActionLabel('Add service')
                        ->reorderable(false)
                        ->collapsible(),
                ]),

            Forms\Components\Section::make('Costs & Notes')->schema([
                Forms\Components\Textarea::make('inspection_notes')->rows(3)->columnSpanFull(),
                Forms\Components\TextInput::make('parts_cost')
                    ->numeric()
                    ->prefix('$')
                    ->default(0)
                    ->live(onBlur: true),
                Forms\Components\TextInput::make('labor_cost')
                    ->numeric()
                    ->prefix('$')
                    ->disabled()
                    ->dehydrated()
                    ->helperText('Auto-calculated from linked services'),
                Forms\Components\TextInput::make('total_cost')
                    ->numeric()
                    ->prefix('$')
                    ->disabled()
                    ->dehydrated()
                    ->helperText('Services + parts'),
            ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('job_number')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label('Customer')->searchable(),
                Tables\Columns\TextColumn::make('vehicle.plate_number')->label('Vehicle'),
                Tables\Columns\TextColumn::make('service.name')->label('Primary Service')->toggleable(),
                Tables\Columns\TextColumn::make('mechanic.name')->label('Mechanic'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => JobCard::statusLabel($state)),
                Tables\Columns\TextColumn::make('total_cost')->formatStateUsing(fn ($state) => Money::format($state)),
                Tables\Columns\TextColumn::make('invoice.invoice_number')
                    ->label('Invoice')
                    ->placeholder('—')
                    ->url(fn (JobCard $record) => $record->invoice
                        ? InvoiceResource::getUrl('edit', ['record' => $record->invoice])
                        : null),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'waiting' => 'Waiting',
                    'diagnosing' => 'Diagnosing',
                    'in_progress' => 'In Progress',
                    'ready_for_pickup' => 'Ready for Pickup',
                    'delivered' => 'Delivered',
                ]),
            ])
            ->actions([
                Tables\Actions\Action::make('invoice')
                    ->label(fn (JobCard $record) => $record->invoice ? 'Update Invoice' : 'Create Invoice')
                    ->icon('heroicon-o-document-text')
                    ->action(function (JobCard $record) {
                        $invoice = app(InvoiceService::class)->createFromJobCard($record->load(['lines', 'service', 'user']));
                        Notification::make()
                            ->title('Invoice '.$invoice->invoice_number.' saved')
                            ->success()
                            ->send();

                        return redirect(InvoiceResource::getUrl('edit', ['record' => $invoice]));
                    }),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJobCards::route('/'),
            'create' => Pages\CreateJobCard::route('/create'),
            'edit' => Pages\EditJobCard::route('/{record}/edit'),
        ];
    }
}
