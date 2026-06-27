<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Filament\Support\CustomerVehicleSync;
use App\Services\CustomerAccountService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected ?string $plainPassword = null;

    protected bool $welcomeEmailSent = false;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['role'] = 'customer';

        $this->plainPassword = app(CustomerAccountService::class)->generatePassword();
        $data['password'] = Hash::make($this->plainPassword);

        unset($data['vehicles']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $state = $this->form->getState();

        CustomerVehicleSync::sync(
            $this->record,
            $state['vehicles'] ?? [],
        );

        $this->welcomeEmailSent = app(CustomerAccountService::class)->sendWelcomeEmail(
            $this->record,
            $this->plainPassword ?? '',
        );

        $this->plainPassword = null;
    }

    protected function getCreatedNotification(): ?Notification
    {
        if ($this->welcomeEmailSent) {
            return Notification::make()
                ->success()
                ->title('Customer saved successfully')
                ->body($this->record->name.' was added. Login credentials were emailed to '.$this->record->email.'.');
        }

        return Notification::make()
            ->warning()
            ->title('Customer saved — email not sent')
            ->body($this->record->name.' was added but the welcome email could not be delivered. Use “Resend login email” on the edit page after checking mail settings.');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
