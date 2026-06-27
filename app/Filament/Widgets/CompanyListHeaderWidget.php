<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class CompanyListHeaderWidget extends Widget
{
    protected static bool $isDiscovered = false;

    protected static string $view = 'filament.widgets.company-list-header';

    protected int|string|array $columnSpan = 'full';

    public ?string $documentTitle = null;

    public ?string $printKey = null;

    public static function canView(): bool
    {
        return true;
    }
}
