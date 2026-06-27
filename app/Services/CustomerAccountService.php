<?php

namespace App\Services;

use App\Mail\CustomerWelcomeMail;
use App\Models\User;
use App\Support\FrontendUrl;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CustomerAccountService
{
    public function generatePassword(): string
    {
        return Str::password(12, letters: true, numbers: true, symbols: false);
    }

    public function loginUrl(): string
    {
        return FrontendUrl::login();
    }

    public function sendWelcomeEmail(User $customer, string $plainPassword): bool
    {
        try {
            Mail::to($customer->email)->send(new CustomerWelcomeMail(
                $customer,
                $plainPassword,
                $this->loginUrl(),
            ));

            return true;
        } catch (\Throwable $e) {
            Log::warning('Customer welcome email failed: '.$e->getMessage(), [
                'user_id' => $customer->id,
                'email' => $customer->email,
            ]);

            return false;
        }
    }

    public function resetCredentialsAndEmail(User $customer): bool
    {
        $plainPassword = $this->generatePassword();

        $customer->update([
            'password' => Hash::make($plainPassword),
        ]);

        return $this->sendWelcomeEmail($customer->fresh(), $plainPassword);
    }
}
