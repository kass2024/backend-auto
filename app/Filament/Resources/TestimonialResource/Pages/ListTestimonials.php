<?php

namespace App\Filament\Resources\TestimonialResource\Pages;

use App\Filament\Pages\BasePrintableListRecords;
use App\Filament\Resources\TestimonialResource;
use Filament\Actions;

class ListTestimonials extends BasePrintableListRecords
{
    protected static string $resource = TestimonialResource::class;

    protected function getListDocumentTitle(): string
    {
        return 'TESTIMONIALS';
    }

    protected function getListPrintKey(): string
    {
        return 'testimonials';
    }

    protected function getResourceHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
