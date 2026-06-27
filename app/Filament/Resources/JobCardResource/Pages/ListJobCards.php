<?php

namespace App\Filament\Resources\JobCardResource\Pages;

use App\Filament\Pages\BasePrintableListRecords;
use App\Filament\Resources\JobCardResource;
use Filament\Actions;

class ListJobCards extends BasePrintableListRecords
{
    protected static string $resource = JobCardResource::class;

    protected function getListDocumentTitle(): string
    {
        return 'JOB CARDS LIST';
    }

    protected function getListPrintKey(): string
    {
        return 'job-cards';
    }

    protected function getResourceHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
