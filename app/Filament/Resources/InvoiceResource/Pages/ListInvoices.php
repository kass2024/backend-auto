<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Pages\BasePrintableListRecords;
use App\Filament\Resources\InvoiceResource;
use Filament\Actions;

class ListInvoices extends BasePrintableListRecords
{
    protected static string $resource = InvoiceResource::class;

    protected function getListDocumentTitle(): string
    {
        return 'INVOICE LIST';
    }

    protected function getListPrintKey(): string
    {
        return 'invoices';
    }

    protected function getResourceHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
