<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\RestrictsStaffAccess;
use App\Filament\Resources\TestimonialResource\Pages;
use App\Models\Testimonial;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TestimonialResource extends Resource
{
    use RestrictsStaffAccess;

    protected static ?string $model = Testimonial::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Website Content';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('customer_name')->required(),
            Forms\Components\TextInput::make('rating')->numeric()->minValue(1)->maxValue(5)->default(5),
            Forms\Components\Textarea::make('review')->required()->columnSpanFull(),
            Forms\Components\TextInput::make('vehicle_info'),
            Forms\Components\TextInput::make('source')->default('Google'),
            Forms\Components\Toggle::make('is_featured')->default(false),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('customer_name')->searchable(),
            Tables\Columns\TextColumn::make('rating')->badge(),
            Tables\Columns\TextColumn::make('review')->limit(50),
            Tables\Columns\IconColumn::make('is_featured')->boolean(),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTestimonials::route('/'),
            'create' => Pages\CreateTestimonial::route('/create'),
            'edit' => Pages\EditTestimonial::route('/{record}/edit'),
        ];
    }
}
