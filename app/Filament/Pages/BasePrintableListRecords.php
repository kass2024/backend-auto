<?php

namespace App\Filament\Pages;

use App\Filament\Support\AdminFlashNotifications;
use App\Filament\Widgets\CompanyListHeaderWidget;
use Filament\Resources\Pages\ListRecords;

abstract class BasePrintableListRecords extends ListRecords
{
    abstract protected function getListDocumentTitle(): string;

    abstract protected function getListPrintKey(): string;

    public function mount(): void
    {
        parent::mount();

        AdminFlashNotifications::showFromSession();
    }

    /** @return array<int, \Filament\Actions\Action> */
    protected function getResourceHeaderActions(): array
    {
        return [];
    }

    public function getHeaderWidgetsColumns(): int|string|array
    {
        return 1;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CompanyListHeaderWidget::make([
                'documentTitle' => $this->getListDocumentTitle(),
                'printKey' => $this->getListPrintKey(),
            ]),
        ];
    }

    protected function getHeaderActions(): array
    {
        return $this->getResourceHeaderActions();
    }
}
