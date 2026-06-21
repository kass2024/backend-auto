<?php

namespace App\Filament\Support;

use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class AdminTableActions
{
    public static function delete(string $itemLabel): Action
    {
        return Action::make('delete')
            ->label('Delete')
            ->color('danger')
            ->icon('heroicon-m-trash')
            ->requiresConfirmation(false)
            ->modalHidden(true)
            ->extraAttributes([
                'wire:confirm' => 'Delete this '.$itemLabel.'? This cannot be undone.',
            ])
            ->action(function (Model $record): void {
                $record->delete();
            })
            ->successNotification(
                Notification::make()
                    ->success()
                    ->title('Deleted successfully')
                    ->body('The '.$itemLabel.' has been removed.')
            );
    }

    public static function deleteBulk(string $itemLabel): BulkAction
    {
        return BulkAction::make('delete')
            ->label('Delete selected')
            ->color('danger')
            ->icon('heroicon-m-trash')
            ->requiresConfirmation(false)
            ->modalHidden(true)
            ->extraAttributes([
                'wire:confirm' => 'Delete selected '.$itemLabel.'? This cannot be undone.',
            ])
            ->action(function (Collection $records): void {
                $records->each->delete();
            })
            ->deselectRecordsAfterCompletion()
            ->successNotification(
                Notification::make()
                    ->success()
                    ->title('Deleted successfully')
                    ->body('Selected '.$itemLabel.' have been removed.')
            );
    }
}
