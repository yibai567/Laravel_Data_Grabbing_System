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
        $createScriptQueue = 1; // 互斥锁存在时间，单位分钟
        $schedule->command('jinse:create_script_queue')->runInBackground()->withoutOverlapping(
            $createScriptQueue
        )->everyMinute();

        $taskAlarmResult = 10; // 互斥锁存在时间，单位分钟
        $schedule->command('task:crawl:over_time_alarm')->runInBackground()->withoutOverlapping(
            $taskAlarmResult
        )->everyTenMinutes();

        //代理监控 每10分钟一次
        $schedule->command('proxy:alarm 127.0.0.1:8123')->runInBackground()->withoutOverlapping(
            $taskAlarmResult
        )->everyTenMinutes();
        //每天十点监测未处理的需求
        $schedule->command('requirement:alarm')->runInBackground()->dailyAt('10:00');
        //每天十点监测任务结果24小时无更新
        $schedule->command('task:result_alarm 24')->runInBackground()->dailyAt('10:00');
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
