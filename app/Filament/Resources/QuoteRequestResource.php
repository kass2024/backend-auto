<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\RestrictsStaffAccess;
use App\Filament\Resources\QuoteRequestResource\Pages;
use App\Filament\Support\AdminTableActions;
use App\Models\QuoteRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class QuoteRequestResource extends Resource
{
    use RestrictsStaffAccess;

    protected static ?string $model = QuoteRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Quote Requests';

    protected static ?string $modelLabel = 'Quote Request';

    protected static ?string $pluralModelLabel = 'Quote Requests';

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'new')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Customer')->schema([
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\TextInput::make('email')->email()->required(),
                Forms\Components\TextInput::make('phone')->tel(),
            ])->columns(3),
            Forms\Components\Section::make('Request details')->schema([
                Forms\Components\Select::make('service_id')->relationship('service', 'name')->searchable(),
                Forms\Components\TextInput::make('vehicle_make')->label('Vehicle make'),
                Forms\Components\TextInput::make('vehicle_model')->label('Vehicle model'),
                Forms\Components\Textarea::make('message')->rows(4)->columnSpanFull(),
                Forms\Components\Select::make('status')->options([
                    'new' => 'New',
                    'contacted' => 'Contacted',
                    'quoted' => 'Quoted',
                    'closed' => 'Closed',
                ])->required(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->color('primary')
                    ->description(fn (QuoteRequest $record) => $record->phone),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-m-envelope'),
                Tables\Columns\TextColumn::make('service.name')
                    ->label('Service')
                    ->placeholder('General inquiry')
                    ->badge()
                    ->color('info')
                    ->searchable(),
                Tables\Columns\TextColumn::make('vehicle')
                    ->label('Vehicle')
                    ->getStateUsing(fn (QuoteRequest $record) => trim("{$record->vehicle_make} {$record->vehicle_model}") ?: '—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('message')
                    ->limit(40)
                    ->tooltip(fn (QuoteRequest $record) => $record->message)
                    ->wrap()
                    ->toggleable(),
                Tables\Columns\SelectColumn::make('status')
                    ->options([
                        'new' => 'New',
                        'contacted' => 'Contacted',
                        'quoted' => 'Quoted',
                        'closed' => 'Closed',
                    ])
                    ->selectablePlaceholder(false)
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'new' => 'New',
                    'contacted' => 'Contacted',
                    'quoted' => 'Quoted',
                    'closed' => 'Closed',
                ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                AdminTableActions::delete('quote request'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    AdminTableActions::deleteBulk('quote requests'),
                ]),
            ])
            ->emptyStateHeading('No quote requests yet')
            ->emptyStateDescription('New quote requests from the website will appear here.')
            ->emptyStateIcon('heroicon-o-inbox');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuoteRequests::route('/'),
            'create' => Pages\CreateQuoteRequest::route('/create'),
            'view' => Pages\ViewQuoteRequest::route('/{record}'),
            'edit' => Pages\EditQuoteRequest::route('/{record}/edit'),
        ];
    }
}
