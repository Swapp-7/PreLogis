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
        // $schedule->command('inspire')->hourly();
        $schedule->command('residents:supprimer-archives')->daily();
        $schedule->command('residents:remove-departed')->dailyAt('00:00');
        $schedule->command('chambres:assigner')->dailyAt('00:01');
        $schedule->command('files:cleanup-temp')->hourly();
        
        // Incrémenter les années d'étude chaque 1er septembre à 02:00
        $schedule->command('residents:increment-annee-etude')
                 ->dailyAt('02:00')
                 ->when(function () {
                     return now()->month === 9 && now()->day === 1;
                 });
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
