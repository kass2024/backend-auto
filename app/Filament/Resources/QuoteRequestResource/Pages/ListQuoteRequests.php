<?php

namespace App\Filament\Resources\QuoteRequestResource\Pages;

use App\Filament\Pages\BasePrintableListRecords;
use App\Filament\Resources\QuoteRequestResource;
use Filament\Actions;

class ListQuoteRequests extends BasePrintableListRecords
{
    protected static string $resource = QuoteRequestResource::class;

    protected function getListDocumentTitle(): string
    {
        return 'QUOTE REQUESTS';
    }

    protected function getListPrintKey(): string
    {
        return 'quote-requests';
    }

    protected function getResourceHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
