<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\RestrictsStaffAccess;
use App\Filament\Resources\PromotionResource\Pages;
use App\Filament\Support\Money;
use App\Models\Promotion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PromotionResource extends Resource
{
    use RestrictsStaffAccess;

    protected static ?string $model = Promotion::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';

    protected static ?string $navigationGroup = 'Website Content';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->required(),
            Forms\Components\Textarea::make('description')->columnSpanFull(),
            Forms\Components\TextInput::make('image')->label('Image URL'),
            Forms\Components\TextInput::make('discount_percent')->numeric()->suffix('%'),
            Forms\Components\TextInput::make('discount_amount')->numeric()->prefix('$'),
            Forms\Components\DatePicker::make('starts_at'),
            Forms\Components\DatePicker::make('ends_at'),
            Forms\Components\Toggle::make('is_featured')->default(false),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('title')->searchable(),
            Tables\Columns\TextColumn::make('discount_percent')->suffix('%'),
            Tables\Columns\TextColumn::make('discount_amount')->formatStateUsing(fn ($state) => $state ? Money::format($state) : '—'),
            Tables\Columns\IconColumn::make('is_featured')->boolean(),
            Tables\Columns\IconColumn::make('is_active')->boolean()->label('Live'),
            Tables\Columns\TextColumn::make('ends_at')->date(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPromotions::route('/'),
            'create' => Pages\CreatePromotion::route('/create'),
            'edit' => Pages\EditPromotion::route('/{record}/edit'),
        ];
    }
}
