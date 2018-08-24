<?php

namespace App\Console\Commands;

use App\Models\V2\AlarmResult;
use App\Models\V2\Task;
use App\Models\V2\TaskLastRunLog;
use App\Services\InternalAPIV2Service;
use Illuminate\Console\Command;
use Log;

class TaskCrawlOverTimeAlarm extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:crawl:over_time_alarm';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'task crawl over time alarm process';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //获取启动中的任务
        $tasks = Task::select('id', 'script_id', 'name', 'cron_type', 'data_type')->where('status', Task::STATUS_START)->get()->toArray();

        if (empty($tasks)) {
            Log::debug('[TaskCrawlOverTimeAlarm handle] task of running is empty');
            return true;
        }

        foreach($tasks as $task) {
            $taskLastRunLog = TaskLastRunLog::select('last_job_at', 'created_at')
                                    ->where('task_id', $task['id'])
                                    ->orderBy('id', 'desc')
                                    ->first();
            if (empty($taskLastRunLog)) {
                Log::debug('[TaskCrawlOverTimeAlarm handle] $taskLastRunLog is not exist, task_id = ' . $task['id']);
                continue;
            }

            //如果是只运行一次的任务，且有最后执行时间的证明脚本正常
            if ($task['cron_type'] == Task::CRON_TYPE_KEEP_ONCE && $taskLastRunLog->last_job_at) {
                continue;
            }

            //如果是刚创建的任务，最后执行时间可能不存在，所以可以使用创建时间来确定
            $lastJobAt = $taskLastRunLog->last_job_at;

            if (!$lastJobAt) {
                $lastJobAt = $taskLastRunLog->created_at;
            }

            $alarmTime = 60 * 15;
            if ($task['data_type'] == Task::DATA_TYPE_CASPERJS) {
                $alarmTime = 60 * 60;
            }

            //任务最后一次执行时间超过15分钟触发报警通知
            if (time()- strtotime($lastJobAt) > $alarmTime) {
                $data = [
                    'type' => AlarmResult::TYPE_WEWORK,
                    'content' => '任务已超过15分钟未执行成功,任务id: ' . $task['id'] . ',脚本id: ' . $task['script_id'] . ',脚本名称: ' . $task['name'] . '，脚本类型：'. Task::DATA_TYPE_ARRAY[$task['data_type']] . '，最后执行时间: ' . $lastJobAt,
                    'wework' => config('alarm.alarm_recipient')
                ];

                $result = InternalAPIV2Service::post('/alarm_result', $data);
                if (!$result) {
                    Log::debug('[TaskCrawlOverTimeAlarm handle] create alarm_result is failed');
                    return false;
                }
            }
        }

        return true;

    }

}