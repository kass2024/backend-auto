<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JobCardResource\Pages;
use App\Models\JobCard;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class JobCardResource extends Resource
{
    protected static ?string $model = JobCard::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Job Cards';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('job_number')->required(),
            Forms\Components\Select::make('user_id')->relationship('user', 'name')->required()->searchable(),
            Forms\Components\Select::make('vehicle_id')->relationship('vehicle', 'plate_number')->required()->searchable(),
            Forms\Components\Select::make('mechanic_id')->relationship('mechanic', 'name')->searchable(),
            Forms\Components\Select::make('booking_id')->relationship('booking', 'reference')->searchable(),
            Forms\Components\Select::make('status')->options([
                'waiting' => 'Waiting',
                'diagnosing' => 'Diagnosing',
                'parts_ordered' => 'Parts Ordered',
                'in_progress' => 'In Progress',
                'quality_check' => 'Quality Check',
                'ready_for_pickup' => 'Ready for Pickup',
                'delivered' => 'Delivered',
            ])->required(),
            Forms\Components\Textarea::make('inspection_notes')->columnSpanFull(),
            Forms\Components\TextInput::make('labor_cost')->numeric()->prefix('$'),
            Forms\Components\TextInput::make('parts_cost')->numeric()->prefix('$'),
            Forms\Components\TextInput::make('total_cost')->numeric()->prefix('$'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('job_number')->searchable(),
                Tables\Columns\TextColumn::make('user.name')->label('Customer'),
                Tables\Columns\TextColumn::make('vehicle.plate_number')->label('Vehicle'),
                Tables\Columns\TextColumn::make('mechanic.name')->label('Mechanic'),
                Tables\Columns\BadgeColumn::make('status')->formatStateUsing(fn ($state) => JobCard::statusLabel($state)),
                Tables\Columns\TextColumn::make('total_cost')->money('usd'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'waiting' => 'Waiting',
                    'diagnosing' => 'Diagnosing',
                    'in_progress' => 'In Progress',
                    'ready_for_pickup' => 'Ready for Pickup',
                    'delivered' => 'Delivered',
                ]),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJobCards::route('/'),
            'create' => Pages\CreateJobCard::route('/create'),
            'edit' => Pages\EditJobCard::route('/{record}/edit'),
        ];
    }
}
