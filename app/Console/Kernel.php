<?php

namespace App\Console;

use App\Console\Commands\ClearDelayQueuJobCommand;
use App\Console\Commands\CrontjobCommand;
use App\Console\Commands\Dev\ShowTableFieldAdvCommand;
use App\Console\Commands\Dev\ShowTableFieldCommand;
use App\Console\Commands\Dev\ShowTableListCommand;
use App\Console\Commands\Dev\TestCommand;
use App\Console\Commands\Dev\TestPushCommand;
use App\Console\Commands\SendBillByEmailCmd;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        CrontjobCommand::class,
        ShowTableFieldCommand::class,
        ShowTableFieldAdvCommand::class,
        ShowTableListCommand::class,

        // Test
        TestCommand::class,

        ClearDelayQueuJobCommand::class,
        SendBillByEmailCmd::class,



    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();

        $schedule->command('crontjob:test')->hourly();
        $schedule->command('admin:clear-job')->everyMinute();
        $schedule->command('email:send-bill')->everyMinute();



        $schedule->command('queue:work --once --timeout=120')->everyMinute()->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
