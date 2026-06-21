<?php

namespace App\Filament\Support;

use Filament\Notifications\Notification;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;

class AdminTableActions
{
    public static function delete(string $itemLabel): DeleteAction
    {
        return DeleteAction::make()
            ->modalHeading('Delete '.$itemLabel.'?')
            ->modalDescription('This cannot be undone.')
            ->modalSubmitActionLabel('Yes, delete')
            ->successNotification(
                Notification::make()
                    ->success()
                    ->title('Deleted successfully')
                    ->body('The '.$itemLabel.' has been removed.')
            );
    }

    public static function deleteBulk(string $itemLabel): DeleteBulkAction
    {
        return DeleteBulkAction::make()
            ->modalHeading('Delete selected '.$itemLabel.'?')
            ->modalSubmitActionLabel('Yes, delete')
            ->successNotification(
                Notification::make()
                    ->success()
                    ->title('Deleted successfully')
                    ->body('Selected '.$itemLabel.' have been removed.')
            );
    }
}
