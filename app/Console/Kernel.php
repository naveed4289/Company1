<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     */
    protected $commands = [
        // yahan apne commands register kar sakte ho
        \App\Console\Commands\DeleteUnverifiedUsers::class,
    ];

    /**
     * Define the application's command schedule.
     */
   protected function schedule(Schedule $schedule)
{
    $schedule->command('users:delete-unverified')
        ->dailyAt('00:00')
        ->timezone('Asia/Karachi'); // Set your appropriate timezone
    
    // Test command - remove after verification
  // For testing purposes only - remove after verification
    $schedule->command('users:delete-unverified')
        ->everyMinute()
        ->appendOutputTo(storage_path('logs/scheduler.log'));
}

    /**
     * Register the commands for the application.
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
