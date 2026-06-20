<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\DatabaseBootstrapper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class BootstrapDatabase extends Command
{
    protected $signature = 'app:bootstrap-db';

    protected $description = 'Create MySQL database, run migrations, and seed demo data';

    public function handle(): int
    {
        Cache::forget('database.bootstrap.v1');

        DatabaseBootstrapper::ensureDatabaseExists();
        $this->info('Database ensured.');

        Artisan::call('migrate', ['--force' => true]);
        $this->line(Artisan::output());

        if (Schema::hasTable('users') && User::query()->count() === 0) {
            Artisan::call('db:seed', ['--force' => true]);
            $this->info('Seeded demo data.');
        }

        Cache::put('database.bootstrap.v1', true, now()->addHour());
        $this->info('Bootstrap complete.');

        return self::SUCCESS;
    }
}
