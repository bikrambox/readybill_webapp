<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

    protected $commands = [
        \App\Console\Commands\ClearLogs::class,
        \App\Console\Commands\ClearTables::class,
    ];


    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('subscription:check-expiry')->everyMinute();

        // Schedule to run daily at 10:00 AM
        $schedule->command('trigger:push-notification')->dailyAt('10:00')
            ->withoutOverlapping();

        // Schedule to run daily at 7:30 PM
        // $schedule->command('trigger:push-notification')->dailyAt('19:05');

        // // Schedule to run weekly On Monday at 2:00 AM
        // $schedule->command('clear:logs')->weeklyOn(1,'2:00');

        // Schedule to run daily at 2:00 AM
        $schedule->command('clear:logs')->dailyAt('02:00')
            ->withoutOverlapping();

        // Schedule to run daily at 2:00 AM
        $schedule->command('clear:tables')->dailyAt('02:00');

        // // Schedule to run every minute
        // $schedule->command('reports:generate')->everyMinute();

        // // Schedule to run daily at 2:30 AM and 3:00 AM
        // $schedule->command('transaction-reports')->dailyAt('02:30');
        // $schedule->command('transaction-reports')->dailyAt('03:00');
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