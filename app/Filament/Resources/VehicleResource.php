<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\RestrictsStaffAccess;
use App\Filament\Resources\VehicleResource\Pages;
use App\Filament\Support\AdminTableActions;
use App\Filament\Resources\UserResource;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VehicleResource extends Resource
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

    protected static ?string $model = Vehicle::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'CRM';

    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isFullAdmin() ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->isFullAdmin() ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->isFullAdmin() ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Vehicle details')
                ->description('Record vehicles here — link to a customer later when creating their account.')
                ->schema([
                    Forms\Components\TextInput::make('plate_number')
                        ->label('Plate number')
                        ->required()
                        ->maxLength(255),
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
                    Forms\Components\TextInput::make('color')
                        ->maxLength(255),
                    Forms\Components\TextInput::make('mileage')
                        ->numeric()
                        ->integer()
                        ->minValue(0),
                    Forms\Components\Textarea::make('notes')
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('user.name')
                ->label('Customer')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('plate_number')
                ->label('Plate number')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('make')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('model')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('year')
                ->sortable(),
            Tables\Columns\TextColumn::make('color')
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('mileage')
                ->sortable(),
        ])->actions([
            Tables\Actions\Action::make('customer')
                ->label('Customer')
                ->icon('heroicon-o-user')
                ->color('gray')
                ->url(fn (Vehicle $record) => $record->user_id
                    ? UserResource::getUrl('edit', ['record' => $record->user_id])
                    : null)
                ->visible(fn (Vehicle $record): bool => filled($record->user_id))
                ->openUrlInNewTab(false),
            AdminTableActions::delete('vehicle'),
        ])
          ->bulkActions([
              Tables\Actions\BulkActionGroup::make([
                  AdminTableActions::deleteBulk('vehicles'),
              ]),
          ])
          ->defaultSort('plate_number');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVehicles::route('/'),
            'create' => Pages\CreateVehicle::route('/create'),
            'edit' => Pages\EditVehicle::route('/{record}/edit'),
        ];
    }
}
