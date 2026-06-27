<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('appointments:send-reminders')->dailyAt('09:00');
        // cPanel hosts often limit cron to every 5 minutes — one job runs all reminder types
        // (minutes/hours/days alerts + weekly/monthly/yearly repeats) via sendDueReminders().
        $schedule->command('invoices:send-service-reminders')->everyFiveMinutes();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
