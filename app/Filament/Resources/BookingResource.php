<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Models\Booking;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('reference')->required()->maxLength(255),
            Forms\Components\Select::make('service_id')->relationship('service', 'name')->required()->searchable(),
            Forms\Components\Select::make('user_id')->relationship('user', 'name')->searchable(),
            Forms\Components\Select::make('vehicle_id')->relationship('vehicle', 'plate_number')->searchable(),
            Forms\Components\Select::make('mechanic_id')->relationship('mechanic', 'name')->searchable(),
            Forms\Components\TextInput::make('customer_name')->required(),
            Forms\Components\TextInput::make('customer_email')->email()->required(),
            Forms\Components\TextInput::make('customer_phone')->tel()->required(),
            Forms\Components\DatePicker::make('scheduled_date')->required(),
            Forms\Components\TimePicker::make('scheduled_time')->required(),
            Forms\Components\Select::make('status')->options([
                'pending' => 'Pending',
                'confirmed' => 'Confirmed',
                'cancelled' => 'Cancelled',
                'completed' => 'Completed',
                'no_show' => 'No Show',
            ])->required(),
            Forms\Components\Textarea::make('notes')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('customer_name')->searchable(),
                Tables\Columns\TextColumn::make('service.name')->sortable(),
                Tables\Columns\TextColumn::make('scheduled_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('scheduled_time'),
                Tables\Columns\BadgeColumn::make('status')->colors([
                    'warning' => 'pending',
                    'success' => 'confirmed',
                    'danger' => 'cancelled',
                    'primary' => 'completed',
                ]),
            ])
            ->defaultSort('scheduled_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'pending' => 'Pending',
                    'confirmed' => 'Confirmed',
                    'cancelled' => 'Cancelled',
                    'completed' => 'Completed',
                ]),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
        ];
    }
}
