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
        Commands\AutomateTask::class,
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $crawlTaskAutomate = 1; // 互斥锁存在时间，单位分钟
        $schedule->command('crawl:task:automate')->runInBackground()->withoutOverlapping(
            $crawlTaskAutomate
        )->everyMinute();

        $itemCrawlAlarm = 1; // 互斥锁存在时间，单位分钟
        $schedule->command('item:crawl:alarm')->runInBackground()->withoutOverlapping(
            $itemCrawlAlarm
        )->everyMinute();

        $createScriptQueue = 1; // 互斥锁存在时间，单位分钟
        $schedule->command('jinse:create_script_queue')->runInBackground()->withoutOverlapping(
            $createScriptQueue
        )->everyMinute();

        $taskAlarmResult = 10; // 互斥锁存在时间，单位分钟
        $schedule->command('task:crawl:over_time_alarm')->runInBackground()->withoutOverlapping(
            $taskAlarmResult
        )->everyTenMinutes();
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
