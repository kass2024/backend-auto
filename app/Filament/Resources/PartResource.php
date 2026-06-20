<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PartResource\Pages;
use App\Models\Part;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PartResource extends Resource
{
    protected static ?string $model = Part::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Inventory';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('sku')->required()->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\Textarea::make('description')->columnSpanFull(),
            Forms\Components\TextInput::make('barcode'),
            Forms\Components\Select::make('supplier_id')->relationship('supplier', 'name')->searchable(),
            Forms\Components\TextInput::make('quantity')->numeric()->default(0),
            Forms\Components\TextInput::make('min_stock')->numeric()->default(5),
            Forms\Components\TextInput::make('unit_cost')->numeric()->prefix('$'),
            Forms\Components\TextInput::make('unit_price')->numeric()->prefix('$'),
            Forms\Components\TextInput::make('location'),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('sku')->searchable(),
            Tables\Columns\TextColumn::make('name')->searchable(),
            Tables\Columns\TextColumn::make('quantity')
                ->color(fn (Part $record) => $record->isLowStock() ? 'danger' : null),
            Tables\Columns\TextColumn::make('min_stock'),
            Tables\Columns\TextColumn::make('unit_price')->money('usd'),
            Tables\Columns\TextColumn::make('supplier.name'),
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
