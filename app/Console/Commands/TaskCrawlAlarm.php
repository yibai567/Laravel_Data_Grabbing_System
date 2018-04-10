<?php

namespace App\Console\Commands;

use App\Services\RequestService;
use App\Models\CrawlTask;
use App\Models\Alarm;
use App\Models\AlarmRule;
use App\Jobs\TaskAlarmSend;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class TaskCrawlAlarm extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:crawl:alarm';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'task crawl alarm process';

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
        $taskList = $this->__taskList();
        if (empty($taskList)) {
            echo "没有启动中的任务 \n";
            return false;
        }

        $alarmRuleList = $this->__alarmRuleList();
        if (empty($alarmRuleList)) {
            echo "没有报警规则 \n";
            return false;
        }

        $time = time();
        foreach ($taskList as $taskValue) {
            $timeDifference = $time - strtotime($taskValue['last_job_at']);
            foreach ($alarmRuleList as $ruleValue) {
                $ruleValue['crawl_task_id'] = $taskValue['id'];
                $ruleValue['limit_number'] = 2;
                switch ($ruleValue['expression']) {
                    case '>':
                        if ($timeDifference > $ruleValue['expression_value']) {
                            echo sprintf("description = %s, task_id = %s \n", $ruleValue['description'], $taskValue['id']);
                            $this->__saveAlarmMessage($ruleValue);
                        }
                        break;
                    case '<':
                        if ($timeDifference < $ruleValue['expression_value']) {
                            $this->__saveAlarmMessage($ruleValue);
                        }
                        break;
                    case '=':
                        if ($timeDifference = $ruleValue['expression_value']) {
                            $this->__saveAlarmMessage($ruleValue);
                        }
                        break;
                    case '>=':
                        if ($timeDifference >= $ruleValue['expression_value']) {
                            $this->__saveAlarmMessage($ruleValue);
                        }
                        break;

                    case '<=':
                        if ($timeDifference <= $ruleValue['expression_value']) {
                            $this->__saveAlarmMessage($ruleValue);
                        }
                        break;

                    default:
                        echo sprintf("条件不符合 \n");
                        break;
                }
            }
        }
    }

    /**
     * __taskList
     * 获取任务列表
     *
     * @return array
     */
    private function __taskList()
    {
        $taskList = [];
        $res = CrawlTask::select('id', 'last_job_at')->where('status', CrawlTask::IS_START_UP)->get();

        if (!empty($res)) {
            $taskList = $res->toArray();
        }

        return $taskList;
    }

    /**
     * __sendAlarm
     * 获取报警规则列表
     *
     * @return array
     */
    private function __saveAlarmMessage($alarmRule)
    {
        //数据库存储参数
        $alarmParams['alarm_rule_id'] = $alarmRule['id'];
        $alarmParams['content'] = $alarmRule['description'] . " 任务ID=" . $alarmRule['crawl_task_id'];
        $alarmParams['status'] = Alarm::IS_INIT;
        $alarmParams['crawl_task_id'] = $alarmRule['crawl_task_id'];

        $res = Alarm::select('id')->where('alarm_rule_id', $alarmRule['id'])
                                    ->where('crawl_task_id', $alarmParams['crawl_task_id'])
                                    ->whereIn('status', [Alarm::IS_INIT, Alarm::IS_PROCESSING])
                                    ->count();
        if ($res >= $alarmRule['limit_number']) {
            return false;
        }

        try {
            $alarmId = Alarm::insertGetId($alarmParams);
            if (empty($alarmId)) {
                return false;
            }
            //报警发送参数
            $newAlarmParams['content'] =  $alarmRule['description'] . "\n任务ID = " . $alarmRule['crawl_task_id'] . "\n报警结果ID = " . $alarmId;
            $newAlarmParams['receive_email'] = $alarmRule['receive_email'];
            $newAlarmParams['receive_phone'] = $alarmRule['receive_phone'];
            $newAlarmParams['receive_wework'] = $alarmRule['receive_wework'];

            TaskAlarmSend::dispatch(json_encode($newAlarmParams));

        } catch (\Exception $e) {
            return false;
        }


        return true;
    }

    /**
     * __alarmRuleList
     * 获取报警规则列表
     *
     * @return array
     */
    private function __alarmRuleList()
    {
        $alarmRuleList = [];
        $res = AlarmRule::get();

        if (!empty($res)) {
            $alarmRuleList = $res->toArray();
        }

        return $alarmRuleList;
    }
}
