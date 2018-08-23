<?php

namespace App\Http\Controllers\API\V2;

use App\Models\V2\AlarmResult;
use Log;
use App\Services\InternalAPIV2Service;
use App\Services\ValidatorService;
use Illuminate\Http\Request;

class AlarmResultController extends Controller
{
    /**
     * batchHandle
     * 采集数据处理
     *
     * @param
     * @return boolean
     */
    public function handle(Request $request)
    {
        $params = $request->all();
        ValidatorService::check($params, [
            'content'         => 'required|string|max:200',
            'task_run_log_id' => 'required|integer|max:9999999999',
        ]);

        //调取根据task_run_log_id查询task_run_log信息
        $selectTaskRunLogData['id'] = $params['task_run_log_id'];
        $taskRunLog = InternalAPIV2Service::get('/task_run_log', $selectTaskRunLogData);

        if (empty($taskRunLog)) {
            Log::debug('[V2/AlarmResultController handle] taskRunLog is not found, task_run_log_id:' . $params['task_run_log_id']);
            return $this->resObjectGet(false, 'alarm_result', $request->path());
        }

        //获取任务信息
        $task = InternalAPIV2Service::get('/task', ['id' => $taskRunLog['task_id']]);

        if (empty($task)) {
            Log::debug('[V2/AlarmResultController handle] task is not found, task id: ' . $taskRunLog['task_id']);
            return $this->resObjectGet(false, 'alarm_result', $request->path());
        }
        //记录脚本运行记录（防止重复报警）
        InternalAPIV2Service::post('/task_last_run_log/update', ['task_id' => $task['id']]);

        //写入报警信息
        $alarmResult = [
            'type' => AlarmResult::TYPE_WEWORK,
            'wework' => config('alarm.alarm_recipient'),
            'content' => $params['content'] . ' 任务id: ' . $task['id'] . ',脚本id: ' . $task['script_id'] . ',脚本名称: ' . $task['name']
        ];

        InternalAPIV2Service::post('/alarm_result', $alarmResult);
        return $this->resObjectGet(true, 'alarm_result', $request->path());
    }

}