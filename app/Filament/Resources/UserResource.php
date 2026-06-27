<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\RestrictsStaffAccess;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserResource extends Resource
{
    use RestrictsStaffAccess;

    protected static function staffNavigation(): bool
    {
        return true;
    }

    protected static function staffViewOnly(): bool
    {
        return true;
    }

    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'CRM';

    protected static ?string $navigationLabel = 'Customers';

    protected static ?string $modelLabel = 'Customer';

    protected static ?string $pluralModelLabel = 'Customers';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('role', 'customer')
            ->with('vehicles');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Customer profile')
                ->description('Basic contact details for the customer account.')
                ->schema([
                    Forms\Components\TextInput::make('name')->required()->maxLength(255),
                    Forms\Components\TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('phone')->tel(),
                    Forms\Components\Textarea::make('address')->columnSpanFull(),
                    Forms\Components\DatePicker::make('birthday'),
                    Forms\Components\TextInput::make('loyalty_points')
                        ->numeric()
                        ->default(0)
                        ->helperText('Reward points for loyalty program'),
                ])->columns(2),
            Forms\Components\Section::make('Vehicles')
                ->description('Add one or more vehicles for this customer. Each plate number must be unique across the garage.')
                ->schema([
                    Forms\Components\Repeater::make('vehicles')
                        ->label('Customer vehicles')
                        ->schema([
                            Forms\Components\Hidden::make('id'),
                            Forms\Components\TextInput::make('plate_number')
                                ->label('Plate number')
                                ->required()
                                ->maxLength(255)
                                ->rule(fn (Get $get) => Rule::unique(Vehicle::class, 'plate_number')->ignore($get('id'))),
                            Forms\Components\TextInput::make('make')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('model')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('year')
                                ->numeric()
                                ->minValue(1900)
                                ->maxValue((int) date('Y') + 1),
                            Forms\Components\TextInput::make('color')->maxLength(255),
                            Forms\Components\TextInput::make('mileage')
                                ->numeric()
                                ->integer()
                                ->minValue(0),
                            Forms\Components\Textarea::make('notes')
                                ->columnSpanFull(),
                        ])
                        ->columns(3)
                        ->defaultItems(0)
                        ->addActionLabel('Add vehicle')
                        ->collapsible()
                        ->itemLabel(fn (?array $state): ?string => filled($state['plate_number'] ?? null) ? $state['plate_number'] : 'New vehicle')
                        ->columnSpanFull(),
                ]),
            Forms\Components\Section::make('Account access')
                ->schema([
                    Forms\Components\Placeholder::make('portal_login_info')
                        ->label('Portal login')
                        ->content('A secure password is generated automatically and emailed to the customer with a sign-in link.')
                        ->visible(fn (string $operation): bool => $operation === 'create'),
                    Forms\Components\TextInput::make('password')
                        ->password()
                        ->revealable()
                        ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                        ->dehydrated(fn ($state) => filled($state))
                        ->visible(fn (string $operation): bool => $operation === 'edit')
                        ->helperText('Leave blank to keep the current password. Use “Resend login email” to generate a new one.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('email')->searchable()->copyable(),
            Tables\Columns\TextColumn::make('phone')->searchable(),
            Tables\Columns\TextColumn::make('vehicles.plate_number')
                ->label('Vehicles')
                ->badge()
                ->separator(',')
                ->limitList(3)
                ->placeholder('—'),
            Tables\Columns\TextColumn::make('invoices_count')
                ->counts('invoices')
                ->label('Invoices'),
            Tables\Columns\TextColumn::make('loyalty_points')
                ->label('Points')
                ->sortable()
                ->badge()
                ->color('success'),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
        ])->actions([
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make(),
        ])
          ->bulkActions([
              Tables\Actions\BulkActionGroup::make([
                  Tables\Actions\DeleteBulkAction::make(),
              ]),
          ])
          ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
