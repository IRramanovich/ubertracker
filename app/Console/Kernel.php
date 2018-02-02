<?php

namespace App\Console;

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
        //
        \App\Console\Commands\ChatServer::class,
        \App\Console\Commands\PushServer::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            $shiftsOffline = Shifts::OfflineDrivers()->with('driver')->get();
            foreach($shiftsOffline as $one){
                $one->closeShift();
            }
        })->everyTenMinutes()
//            ->between('7:00', '8:00')
            ->between('17:30', '19:00');

        $schedule->call(function () {

        })->monthly();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
