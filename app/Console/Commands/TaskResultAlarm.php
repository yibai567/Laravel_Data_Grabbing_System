<?php

namespace App\Console\Commands;

use App\Models\V2\AlarmResult;
use App\Models\V2\Data;
use App\Models\V2\Task;
use App\Models\V2\TaskLastRunLog;
use App\Services\InternalAPIV2Service;
use Illuminate\Console\Command;
use Log;

class TaskResultAlarm extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:result_alarm {hour : Monitored time range, consistent with script run time}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitoring task results';

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
        $tasks = Task::select('id', 'script_id', 'name', 'cron_type')->where('status', Task::STATUS_START)->get()->toArray();

        if (empty($tasks)) {
            Log::debug('[TaskCrawlOverTimeAlarm handle] task of running is empty');
            return true;
        }

        $hour = $this->argument('hour');

        foreach($tasks as $task) {
            $alarm = [];
            $data = Data::select('updated_at')
                ->where('task_id', $task['id'])
                ->orderBy('updated_at', 'desc')
                ->first();
            //如果没有数据结果，查看是否有脚本运行记录
            if (empty($data)) {
                $taskLastRunLog = TaskLastRunLog::select('last_job_at', 'created_at')
                    ->where('task_id', $task['id'])
                    ->orderBy('id', 'desc')
                    ->first();
                if (!$taskLastRunLog || !$taskLastRunLog->last_job_at) {
                    continue;
                }
                $alarm['content'] = '任务脚本已运行完毕，结果未产生数据,任务id: ' . $task['id'] . ',脚本id: ' . $task['script_id'] . ',脚本名称: ' . $task['name'] . '，脚本最后运行时间为：' . $taskLastRunLog->last_job_at;
            } else if (time() - strtotime($data->updated_at) >= 60 * 60 * $hour) {
                $alarm['content'] = '任务结果已超过' . $hour. '小时未更新,任务id: ' . $task['id'] . ',脚本id: ' . $task['script_id'] . ',脚本名称: ' . $task['name'];
            }

            if (empty($alarm)) {
                continue;
            }

            $alarm['type'] = AlarmResult::TYPE_WEWORK;
            $alarm['wework'] = config('alarm.alarm_recipient');
            $result = InternalAPIV2Service::post('/alarm_result', $alarm);
            if (!$result) {
                Log::debug('[TaskCrawlOverTimeAlarm handle] create alarm_result is failed');
            }
        }

        return true;
    }
}
