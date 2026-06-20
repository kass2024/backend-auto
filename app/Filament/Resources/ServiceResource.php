<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\RestrictsStaffAccess;
use App\Filament\Resources\ServiceResource\Pages;
use App\Filament\Support\Money;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ServiceResource extends Resource
{
    use RestrictsStaffAccess;

    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Website Content';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Services';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Service (shown on public website)')->schema([
                Forms\Components\TextInput::make('name')->required()->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true),
                Forms\Components\Textarea::make('description')->columnSpanFull()
                    ->helperText('Visible on neamee-autotechsolutions.com services page'),
                Forms\Components\TextInput::make('price_from')->numeric()->prefix('$')->required()
                    ->helperText('Starting price shown on the public website'),
                Forms\Components\TextInput::make('duration_minutes')->numeric()->default(60),
                Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
                Forms\Components\Toggle::make('is_active')->default(true)
                    ->helperText('Inactive services are hidden from the public site'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->searchable(),
            Tables\Columns\TextColumn::make('price_from')->formatStateUsing(fn ($state) => Money::format($state))->sortable(),
            Tables\Columns\IconColumn::make('is_active')->boolean()->label('Live on site'),
            Tables\Columns\TextColumn::make('sort_order')->sortable(),
        ])->actions([Tables\Actions\EditAction::make()])
          ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])])
          ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
