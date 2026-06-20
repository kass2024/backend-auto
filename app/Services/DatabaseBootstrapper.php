<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use PDO;
use Throwable;

class DatabaseBootstrapper
{
    public static function run(): void
    {
        if (! filter_var(env('AUTO_MIGRATE', false), FILTER_VALIDATE_BOOLEAN)) {
            return;
        }

        $cacheKey = 'database.bootstrap.v1';

        try {
            self::ensureDatabaseExists();

            if (! Cache::has($cacheKey)) {
                Artisan::call('migrate', ['--force' => true]);
                Cache::put($cacheKey, true, now()->addHour());
            }

            if (Schema::hasTable('users') && User::query()->count() === 0) {
                Artisan::call('db:seed', ['--force' => true]);
            }
        } catch (Throwable $e) {
            Log::warning('Database bootstrap: '.$e->getMessage());
        }
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
