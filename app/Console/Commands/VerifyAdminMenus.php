<?php

namespace App\Console\Commands;

use Filament\Facades\Filament;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;

class VerifyAdminMenus extends Command
{
    protected $signature = 'neamee:verify-admin-menus';

    protected $description = 'Verify all Filament admin resources load without errors';

    public function handle(): int
    {
        $admin = \App\Models\User::where('role', 'admin')->first();
        if (! $admin) {
            $this->error('No admin user found. Run: php artisan db:seed --class=GarageSeeder');

            return self::FAILURE;
        }

        Auth::login($admin);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $resources = [
            \App\Filament\Resources\BookingResource\Pages\ListBookings::class,
            \App\Filament\Resources\JobCardResource\Pages\ListJobCards::class,
            \App\Filament\Resources\QuoteRequestResource\Pages\ListQuoteRequests::class,
            \App\Filament\Resources\UserResource\Pages\ListUsers::class,
            \App\Filament\Resources\VehicleResource\Pages\ListVehicles::class,
            \App\Filament\Resources\InvoiceResource\Pages\ListInvoices::class,
            \App\Filament\Resources\StaffUserResource\Pages\ListStaffUsers::class,
            \App\Filament\Resources\PartResource\Pages\ListParts::class,
            \App\Filament\Resources\ServiceResource\Pages\ListServices::class,
            \App\Filament\Resources\BlogPostResource\Pages\ListBlogPosts::class,
            \App\Filament\Resources\TestimonialResource\Pages\ListTestimonials::class,
            \App\Filament\Resources\PromotionResource\Pages\ListPromotions::class,
        ];

        $failed = 0;
        foreach ($resources as $page) {
            $label = class_basename($page);
            try {
                Livewire::test($page);
                $this->info("OK  {$label}");
            } catch (\Throwable $e) {
                $failed++;
                $this->error("FAIL {$label}: ".$e->getMessage());
            }
        }

        Auth::logout();

        if ($failed > 0) {
            $this->error("{$failed} menu(s) failed.");

            return self::FAILURE;
        }

        $this->info('All admin menus OK.');

        return self::SUCCESS;
    }
}
