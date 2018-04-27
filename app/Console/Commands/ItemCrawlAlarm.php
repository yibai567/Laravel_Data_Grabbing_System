<?php

namespace App\Console\Commands;

use Log;
use App\Services\RequestService;
use App\Models\Item;
use App\Models\Alarm;
use App\Models\AlarmRule;
use App\Jobs\TaskAlarmSend;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class ItemCrawlAlarm extends Command
{
    protected $time;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'item:crawl:alarm';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'item crawl alarm process';

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
        $itemList = [];

        Log::debug('[item:crawl:alarm] start');

        $item = Item::select('id', 'last_job_at')->where('status', Item::STATUS_START)->get();

        $itemList = $item->toArray();

        if (empty($itemList)) {
            Log::debug('[item:crawl:alarm] $itemList empty');
            return true;
        }

        Log::debug('[item:crawl:alarm] get item list', $itemList);

        //获取任务报警规则
        $alarmRuleList = [];
        $alarmRule = AlarmRule::get();

        $alarmRuleList = $alarmRule->toArray();
        if (empty($alarmRuleList)) {
            Log::debug('[item:crawl:alarm] $alarmRuleList empty');
            return false;
        }

        Log::debug('[item:crawl:alarm] get alarmRule list list', $alarmRuleList);

        //报警处理
        foreach ($itemList as $itemValue) {
            //计算当前时间和任务最后执行时间时差
            $timeDifference = $this->time - strtotime($itemValue['last_job_at']);
            Log::debug('[item:crawl:alarm] 时差 $timeDifference = ' . $timeDifference);
            foreach ($alarmRuleList as $ruleValue) {
                $ruleValue['item_id'] = $itemValue['id'];
                $ruleValue['limit_number'] = 1;
                //报警信息发送
                $this->__messageDispatch($timeDifference, $ruleValue);
            }
        }
    }

    private function __messageDispatch($timeDifference, $alarmResult)
    {
        Log::debug('[item:crawl:alarm] $timeDifference = ' . $alarmResult['expression'] . ', $alarmResult', $alarmResult);
        switch ($alarmResult['expression']) {
            case '>':
                Log::debug('[item:crawl:alarm] 表达式 $alarmResult["expression"] = ' . $alarmResult['expression']);
                if ($timeDifference > $alarmResult['expression_value']) {
                    $this->__saveAlarmMessage($alarmResult);
                }
                break;
            case '<':
                Log::debug('[item:crawl:alarm] 表达式 $alarmResult["expression"] = ' . $alarmResult['expression']);
                if ($timeDifference < $alarmResult['expression_value']) {
                    $this->__saveAlarmMessage($alarmResult);
                }
                break;
            case '=':
                Log::debug('[item:crawl:alarm] 表达式 $alarmResult["expression"] = ' . $alarmResult['expression']);
                if ($timeDifference = $alarmResult['expression_value']) {
                    $this->__saveAlarmMessage($alarmResult);
                }
                break;
            case '>=':
                Log::debug('[item:crawl:alarm] 表达式 $alarmResult["expression"] = ' . $alarmResult['expression']);
                if ($timeDifference >= $alarmResult['expression_value']) {
                    $this->__saveAlarmMessage($alarmResult);
                }
                break;

            case '<=':
                Log::debug('[item:crawl:alarm] 表达式 $alarmResult["expression"] = ' . $alarmResult['expression']);
                if ($timeDifference <= $alarmResult['expression_value']) {
                    $this->__saveAlarmMessage($alarmResult);
                }
                break;

            default:
                infoLog('[报警脚本]-表达式条件不符合');
                break;
        }
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
        $alarmParams['content'] = $alarmRule['description'] . " 任务ID=" . $alarmRule['item_id'];
        $alarmParams['status'] = Alarm::IS_INIT;
        $alarmParams['crawl_task_id'] = $alarmRule['item_id'];
        $alarmParams['created_at'] = date('Y-m-d H:i:s');

        $res = Alarm::select('id', 'created_at')->where('alarm_rule_id', $alarmRule['id'])
                                    ->where('crawl_task_id', $alarmParams['crawl_task_id'])
                                    ->whereIn('status', [Alarm::IS_INIT, Alarm::IS_PROCESSING])
                                    ->orderBy('id', 'desc')
                                    ->get();
        $alarmResult = $res->toArray();

        if (!empty($alarmResult)) {
            $hour = $this->time - strtotime($alarmResult[0]['created_at']);
            $count = count($alarmResult);
            $day = 21600;
            if ($count >= $alarmRule['limit_number']) {
                if ($hour < $day) {
                    return true;
                }
            }
        }
        try {
            Log::debug('[item:crawl:alarm] create alarmResult', $alarmParams);
            $alarmId = Alarm::insertGetId($alarmParams);
            if (empty($alarmId)) {
                Log::debug('[item:crawl:alarm] create alarmResult fail', $alarmParams);
                return false;
            }

            $currentEnv = getenv('CURRENT_ENV');

            if ($currentEnv == 'test') {
                $currentEnv = '测试环境';
            } else {
                $currentEnv = '正式环境';
            }
            //报警发送参数
            $newAlarmParams['content'] = "您的监控项：" . "【" . $alarmRule['name'] . "】" . "出现异常\n" . "报警结果ID：" . $alarmId . "\n报警内容：" . $alarmRule['description'] . " 任务ID：" . $alarmRule['item_id'] . "\n当前环境：" . $currentEnv;
            $newAlarmParams['receive_email'] = $alarmRule['receive_email'];
            $newAlarmParams['receive_phone'] = $alarmRule['receive_phone'];
            $newAlarmParams['receive_wework'] = $alarmRule['receive_wework'];

            Log::debug('[item:crawl:alarm] alarm message dispatch', $newAlarmParams);
            TaskAlarmSend::dispatch(json_encode($newAlarmParams));

        } catch (\Exception $e) {
            Log::debug('[item:crawl:alarm] - alarm error', $e);
            return false;
        }


        return true;
    }


}
