<?php

namespace App\Filament\Resources\InvoiceResource\RelationManagers;

use App\Filament\Support\Money;
use App\Services\InvoiceService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Line Items';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('description')->required()->columnSpanFull(),
            Forms\Components\TextInput::make('quantity')->numeric()->default(1)->required()->live(onBlur: true)
                ->afterStateUpdated(fn ($state, callable $set, callable $get) => $set('total', (float) $state * (float) $get('unit_price'))),
            Forms\Components\TextInput::make('unit_price')->numeric()->prefix('$')->required()->live(onBlur: true)
                ->afterStateUpdated(fn ($state, callable $set, callable $get) => $set('total', (float) $get('quantity') * (float) $state)),
            Forms\Components\TextInput::make('total')->numeric()->prefix('$')->disabled()->dehydrated(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('description')->wrap(),
                Tables\Columns\TextColumn::make('quantity'),
                Tables\Columns\TextColumn::make('unit_price')->formatStateUsing(fn ($state) => Money::format($state)),
                Tables\Columns\TextColumn::make('total')->formatStateUsing(fn ($state) => Money::format($state)),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['total'] = (float) ($data['quantity'] ?? 1) * (float) ($data['unit_price'] ?? 0);

                        return $data;
                    })
                    ->after(fn () => app(InvoiceService::class)->recalculateTotals($this->getOwnerRecord())),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['total'] = (float) ($data['quantity'] ?? 1) * (float) ($data['unit_price'] ?? 0);

                        return $data;
                    })
                    ->after(fn () => app(InvoiceService::class)->recalculateTotals($this->getOwnerRecord())),
                Tables\Actions\DeleteAction::make()
                    ->after(fn () => app(InvoiceService::class)->recalculateTotals($this->getOwnerRecord())),
            ]);
    }
}
