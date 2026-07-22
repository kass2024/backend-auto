<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use PDO;
use Throwable;

class DatabaseBootstrapper
{
    /** Tables that must exist once their migrations ship (cPanel often skips artisan migrate). */
    private const CRITICAL_TABLES = [
        'quotations',
        'quotation_items',
    ];

    public static function run(): void
    {
        $autoMigrate = (bool) config('neamee.auto_migrate', true);

        try {
            self::ensureDatabaseExists();

            $needsMigrate = $autoMigrate && self::hasPendingMigrations();
            if (! $needsMigrate) {
                $needsMigrate = self::missingCriticalTables();
            }

            if ($needsMigrate) {
                Artisan::call('migrate', ['--force' => true]);
                Log::info('Database bootstrap: migrations applied.', [
                    'output' => trim(Artisan::output()),
                ]);
            }

            if (Schema::hasTable('users') && User::query()->count() === 0) {
                Artisan::call('db:seed', ['--force' => true]);
            }
        } catch (Throwable $e) {
            Log::warning('Database bootstrap: '.$e->getMessage());
        }
    }

    public static function missingCriticalTables(): bool
    {
        foreach (self::CRITICAL_TABLES as $table) {
            if (! Schema::hasTable($table)) {
                return true;
            }
        }

        return false;
    }

    public static function hasPendingMigrations(): bool
    {
        /** @var Migrator $migrator */
        $migrator = app(Migrator::class);

        if (! $migrator->repositoryExists()) {
            return true;
        }

        $files = $migrator->getMigrationFiles(database_path('migrations'));
        $ran = $migrator->getRepository()->getRan();

        return count(array_diff(array_keys($files), $ran)) > 0;
    }

    public static function ensureDatabaseExists(): void
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        if (($config['driver'] ?? '') !== 'mysql') {
            return;
        }

        $database = $config['database'] ?? null;
        if (! $database) {
            return;
        }

        $charset = $config['charset'] ?? 'utf8mb4';
        $collation = $config['collation'] ?? 'utf8mb4_unicode_ci';

        $pdo = new PDO(
            sprintf(
                'mysql:host=%s;port=%s',
                $config['host'] ?? '127.0.0.1',
                $config['port'] ?? 3306
            ),
            $config['username'] ?? 'root',
            $config['password'] ?? '',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $pdo->exec(
            "CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET {$charset} COLLATE {$collation}"
        );

        Config::set("database.connections.{$connection}.database", $database);
        DB::purge($connection);
    }
}
