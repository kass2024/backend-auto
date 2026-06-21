<?php

namespace App\Filament\Resources\QuoteRequestResource\Pages;

use App\Filament\Resources\QuoteRequestResource;
use App\Models\QuoteRequest;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListQuoteRequests extends ListRecords
{
    protected static string $resource = QuoteRequestResource::class;

    public function getSubheading(): string|Htmlable|null
    {
        $new = QuoteRequest::where('status', 'new')->count();
        $total = QuoteRequest::count();

        return "{$total} total · {$new} new awaiting review";
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
