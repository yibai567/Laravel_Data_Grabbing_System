<?php

namespace App\Console\Commands;

use App\Models\V2\AlarmResult;
use App\Models\V2\Task;
use App\Models\V2\TaskRunLog;
use App\Services\InternalAPIV2Service;
use Illuminate\Console\Command;
use Log;

class TaskCrawlOverTimeAlarm extends Command
{
    protected $time;
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
        $this->time = time();
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
        $tasks = Task::select('id', 'script_id', 'name')->where('status', Task::STATUS_START)->get();

        if (empty($tasks)) {
            Log::debug('[TaskCrawlOverTimeAlarm handle] task of running is empty');
            return true;
        }

        $taskList = $tasks->toArray();


        //查询执行脚本最后一次完成时间
        foreach($taskList as $task) {
            $taskRunLog = TaskRunLog::select('end_job_at')
                                    ->where('task_id', $task['id'])
                                    ->where('status', TaskRunLog::STATUS_SUCCESS)
                                    ->orderBy('id', 'desc')
                                    ->first();
            if (empty($taskRunLog)) {
                Log::debug('[TaskCrawlOverTimeAlarm handle] $taskRunLog is not exist, task_run_log_id = ' . $task['id']);
                continue;
            }
            //判断任务最后一次执行时间是否超过15分钟
            $lastJobAt = strtotime($taskRunLog->end_job_at);

            if ($this->time - $lastJobAt < 60 * 15) {
                continue;
            }
            $data = [];
            $data['type'] = AlarmResult::TYPE_WEWORK;
            $data['content'] = '任务已超过15分钟未执行成功,任务id: ' . $task['id'] . ',脚本id: ' . $task['script_id'] . ',脚本名称: ' . $task['name'] . ',最后执行成功时间: ' . $taskRunLog->end_job_at;
            $data['wework'] = config('alarm.alarm_recipient');

            $result = InternalAPIV2Service::post('/alarm_result', $data);
            if (!$result) {
                Log::debug('[TaskCrawlOverTimeAlarm handle] create alarm_result is failed');
                return false;
            }
        }

        return true;

    }

}