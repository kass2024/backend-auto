<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\RestrictsStaffAccess;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

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
        return parent::getEloquentQuery()->where('role', 'customer');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Customer Profile')->schema([
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
            Forms\Components\Section::make('Account Access')->schema([
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $operation) => $operation === 'create')
                    ->helperText('Set a temporary password — customer can change it after first login.'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('email')->searchable()->copyable(),
            Tables\Columns\TextColumn::make('phone')->searchable(),
            Tables\Columns\TextColumn::make('vehicles_count')
                ->counts('vehicles')
                ->label('Vehicles'),
            Tables\Columns\TextColumn::make('bookings_count')
                ->counts('bookings')
                ->label('Bookings'),
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
