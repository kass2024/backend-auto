<?php

namespace App\Filament\Resources\PromotionResource\Pages;

use App\Filament\Pages\BasePrintableListRecords;
use App\Filament\Resources\PromotionResource;
use Filament\Actions;

class ListPromotions extends BasePrintableListRecords
{
    protected static string $resource = PromotionResource::class;

    protected function getListDocumentTitle(): string
    {
        return 'PROMOTIONS';
    }

    protected function getListPrintKey(): string
    {
        return 'promotions';
    }

    protected function getResourceHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
