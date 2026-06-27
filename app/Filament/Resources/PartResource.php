<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\RestrictsStaffAccess;
use App\Filament\Resources\PartResource\Pages;
use App\Filament\Support\Money;
use App\Models\Part;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PartResource extends Resource
{
    use RestrictsStaffAccess;

    protected static ?string $model = Part::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Inventory';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Part details')
                ->description('Match your inventory sheet — unit price is used on invoices.')
                ->schema([
                    Forms\Components\TextInput::make('part_no')
                        ->label('Part no')
                        ->maxLength(50)
                        ->helperText('Leave blank to auto-generate'),
                    Forms\Components\TextInput::make('category')
                        ->maxLength(255)
                        ->placeholder('e.g. Engine, Brakes, Filters'),
                    Forms\Components\TextInput::make('brand')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('vehicle_model')
                        ->label('Model')
                        ->maxLength(255),
                    Forms\Components\TextInput::make('vehicle_year')
                        ->label('Year')
                        ->numeric()
                        ->minValue(1900)
                        ->maxValue((int) date('Y') + 1),
                    Forms\Components\TextInput::make('manufacturer_part_number')
                        ->label('Part number')
                        ->maxLength(255),
                    Forms\Components\TextInput::make('quantity')
                        ->numeric()
                        ->integer()
                        ->default(0)
                        ->minValue(0)
                        ->required(),
                    Forms\Components\TextInput::make('unit_price')
                        ->label('Unit price')
                        ->numeric()
                        ->prefix('$')
                        ->default(0)
                        ->minValue(0)
                        ->required(),
                    Forms\Components\Toggle::make('is_active')
                        ->default(true)
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('part_no')
                ->label('Part no')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('category')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('brand')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('vehicle_model')
                ->label('Model')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('vehicle_year')
                ->label('Year')
                ->sortable(),
            Tables\Columns\TextColumn::make('manufacturer_part_number')
                ->label('Part number')
                ->searchable(),
            Tables\Columns\TextColumn::make('quantity')
                ->sortable()
                ->color(fn (Part $record) => $record->isLowStock() ? 'danger' : null),
            Tables\Columns\TextColumn::make('unit_price')
                ->label('Unit price')
                ->formatStateUsing(fn ($state) => Money::format($state))
                ->sortable(),
        ])->actions([Tables\Actions\EditAction::make()])
          ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListParts::route('/'),
            'create' => Pages\CreatePart::route('/create'),
            'edit' => Pages\EditPart::route('/{record}/edit'),
        ];
    }
}
