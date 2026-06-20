<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\RestrictsStaffAccess;
use App\Filament\Resources\VehicleResource\Pages;
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

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')->relationship('user', 'name', fn ($q) => $q->where('role', 'customer'))->required()->searchable(),
            Forms\Components\TextInput::make('plate_number')->required(),
            Forms\Components\TextInput::make('make')->required(),
            Forms\Components\TextInput::make('model')->required(),
            Forms\Components\TextInput::make('year')->numeric(),
            Forms\Components\TextInput::make('vin')->maxLength(17),
            Forms\Components\TextInput::make('color'),
            Forms\Components\TextInput::make('mileage')->numeric(),
            Forms\Components\Textarea::make('notes')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('user.name')->label('Owner')->searchable(),
            Tables\Columns\TextColumn::make('plate_number')->searchable(),
            Tables\Columns\TextColumn::make('make'),
            Tables\Columns\TextColumn::make('model'),
            Tables\Columns\TextColumn::make('year'),
            Tables\Columns\TextColumn::make('mileage'),
        ])->actions([
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make(),
        ])
          ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
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
