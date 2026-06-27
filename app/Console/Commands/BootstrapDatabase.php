<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\DatabaseBootstrapper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class BootstrapDatabase extends Command
{
    protected $signature = 'app:bootstrap-db';

    protected $description = 'Create MySQL database, run pending migrations, and seed if empty';

    public function handle(): int
    {
        DatabaseBootstrapper::ensureDatabaseExists();
        $this->info('Database ensured.');

        if (DatabaseBootstrapper::hasPendingMigrations()) {
            Artisan::call('migrate', ['--force' => true]);
            $this->line(Artisan::output());
        } else {
            $this->info('No pending migrations.');
        }

        if (Schema::hasTable('users') && User::query()->count() === 0) {
            Artisan::call('db:seed', ['--force' => true]);
            $this->info('Seeded demo data.');
        }

        $this->info('Bootstrap complete.');

        return self::SUCCESS;
    }
}
