<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Filament\Support\CustomerVehicleSync;
use App\Services\CustomerAccountService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['vehicles'] = $this->record->vehicles()
            ->orderBy('plate_number')
            ->get()
            ->map(fn ($vehicle) => [
                'id' => $vehicle->id,
                'plate_number' => $vehicle->plate_number,
                'make' => $vehicle->make,
                'model' => $vehicle->model,
                'year' => $vehicle->year,
                'color' => $vehicle->color,
                'mileage' => $vehicle->mileage,
                'notes' => $vehicle->notes,
            ])
            ->all();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['vehicles']);

        return $data;
    }

    protected function afterSave(): void
    {
        $state = $this->form->getState();

        CustomerVehicleSync::sync(
            $this->record,
            $state['vehicles'] ?? [],
        );
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Customer saved successfully')
            ->body($this->record->name.' and their vehicles have been updated.');
    }

    protected function getRedirectUrl(): ?string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('resendLoginEmail')
                ->label('Resend login email')
                ->icon('heroicon-o-envelope')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Send new login credentials?')
                ->modalDescription('A new temporary password will be generated and emailed to '.$this->record->email.'.')
                ->action(function (): void {
                    $sent = app(CustomerAccountService::class)->resetCredentialsAndEmail($this->record);

                    if ($sent) {
                        Notification::make()
                            ->success()
                            ->title('Login email sent')
                            ->body('New credentials were emailed to '.$this->record->email.'.')
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->danger()
                        ->title('Email failed')
                        ->body('Could not send login email. Check SMTP settings in .env.')
                        ->send();
                }),
            Actions\DeleteAction::make(),
        ];
    }
}
