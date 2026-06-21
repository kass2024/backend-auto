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

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationGroup = 'Website Content';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Services';

    protected static ?string $pluralModelLabel = 'Services Catalog';

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('is_active', true)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

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
                Forms\Components\TextInput::make('duration_minutes')->numeric()->default(60)->suffix('min'),
                Forms\Components\TextInput::make('sort_order')->numeric()->default(0)
                    ->helperText('Lower numbers appear first in the list'),
                Forms\Components\Toggle::make('is_active')->default(true)
                    ->helperText('Inactive services are hidden from the public site'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Service name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->color('primary')
                    ->description(fn (Service $record) => $record->slug),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->tooltip(fn (Service $record) => $record->description)
                    ->wrap()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('price_from')
                    ->label('From price')
                    ->formatStateUsing(fn ($state) => Money::format($state))
                    ->sortable()
                    ->weight('semibold'),
                Tables\Columns\TextColumn::make('duration_minutes')
                    ->label('Duration')
                    ->formatStateUsing(fn ($state) => $state ? "{$state} min" : '—')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state) => $state ? 'Active' : 'Hidden')
                    ->color(fn (bool $state) => $state ? 'success' : 'gray')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->striped()
            ->reorderable('sort_order')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Visibility')
                    ->trueLabel('Active on site')
                    ->falseLabel('Hidden')
                    ->placeholder('All services'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No services in catalog')
            ->emptyStateDescription('Add services — they appear on the public website and booking forms.')
            ->emptyStateIcon('heroicon-o-wrench-screwdriver');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'view' => Pages\ViewService::route('/{record}'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
