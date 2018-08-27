<?php
/**
 * DataController
 * Data控制器
 * @author liqi@jinse.com
 * @version 1.0
 * Date: 2018/06/15
 */

namespace App\Http\Controllers\API\V2;

use App\Events\SaveDataEvent;
use App\Services\HttpService;
use Log;
use App\Services\InternalAPIV2Service;
use App\Services\ValidatorService;
use Illuminate\Http\Request;

class DataController extends Controller
{
    /**
     * batchHandle
     * 采集数据处理
     *
     * @param
     * @return boolean
     */
    public function batchHandle(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'company'         => 'required|string|max:500',
            'content_type'    => 'required|integer|between:1,9',
            'task_run_log_id' => 'nullable|integer|max:9999999999',
            'start_time'      => 'required|date',
            'end_time'        => 'required|date',
            'result'          => 'required|array'
        ]);

        //如果未传task_run_log_id,是为测试脚本,直接返回结果
        if (empty($params['task_run_log_id'])) {
            return $this->resObjectGet(true, 'data', $request->path());
        }

        if (empty($params['result'])) {//触发报警信息
            $this->__alarm($params['task_run_log_id']);
        }

        $resParams = [
            'company' => $params['company'],
            'task_run_log_id' => $params['task_run_log_id']
        ];
        try {
            //调取根据task_run_log_id查询task_run_log信息
            $selectTaskRunLogData['id'] = $params['task_run_log_id'];
            $taskRunLog = InternalAPIV2Service::get('/task_run_log', $selectTaskRunLogData);

            if (empty($taskRunLog)) {
                Log::debug('[v2 DataController batchHandle]  $taskRunLog is not found,task_run_log_id : ' . $params['task_run_log_id']);
                return $this->resObjectGet(false, 'data', $request->path());
            }

            //查询taskRunLog对应的taskId
            $taskId = $taskRunLog['task_id'];
            $resParams['task_id'] = $taskId;
            $updateTaskStatisticsData['task_id'] = $taskId;
            //记录脚本运行记录
            $result = InternalAPIV2Service::post('/task_last_run_log/update', $updateTaskStatisticsData);

            if (!$result) {

                Log::debug('[v2 DataController batchHandle] update task statistics is failed,task_id : ' . $taskId);
                $updateTaskRunLogData['id'] = $params['task_run_log_id'];
                //更改task_run_log状态
                InternalAPIV2Service::post('/task_run_log/status/fail', $updateTaskRunLogData);

                return $this->resObjectGet(false, 'data', $request->path());
            }

            $params['task_id'] = $taskId;

            $data = InternalAPIV2Service::post('/data', $params);
            Log::debug('[v2 DataController batchHandle] $data = ', $data);

            $dataNum = count($data['data']);
            $updateTaskRunLogData['result_count'] = $dataNum;
            $updateTaskRunLogData['id'] = $params['task_run_log_id'];
            //更改task_run_log状态
            InternalAPIV2Service::post('/task_run_log/status/success', $updateTaskRunLogData);

            //如果数据库存入数据,则调用事件
            if ($dataNum > 0) {
                event(new SaveDataEvent($data));
            }

        } catch (\Exception $e) {
            Log::debug('[v2 DataController batchHandle] error message = ' . $e->getMessage());

            $updateTaskRunLogData['id'] = $params['task_run_log_id'];
            //更改task_runRunLog状态
            InternalAPIV2Service::post('/task_run_log/status/fail', $updateTaskRunLogData);
            Log::debug('[v2 DataController batchHandle] resParams = ', $resParams);
            return $this->resObjectGet(false, 'data', $request->path());
        }

        Log::debug('[v2 DataController batchHandle] resParams = ', $resParams);
        return $this->resObjectGet($resParams, 'data', $request->path());
    }


    public function converter(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'company'         => 'required|string|max:500',
            'content_type'    => 'required|integer|between:1,9',
            'task_run_log_id' => 'required|integer',
            'start_time'      => 'required|date',
            'end_time'        => 'required|date',
            'result'          => 'required|array',
        ]);

        //处理task_run_log_id
        $taskRunLogId = explode('|', $params['task_run_log_id']);

        //如果为零走测试
        if ($taskRunLogId[0] == 0) {

            //是否存在task_run_log_id
            if (!isset($taskRunLogId[1])) {
                Log::debug('[v2 DataController converter] $taskRunLogId[1] is not found, task_run_log_id : ' . $params['task_run_log_id']);
                throw new \Dingo\Api\Exception\ResourceException('task_run_log_id is not found');
            }

            $testData = ['task_id' => $taskRunLogId[1], 'task_result' => $params['result']];

            $name = empty($params['result']) ? 'fail' : 'success';
            Log::debug('[v2 DataController converter] test success, testData = ', $testData);
            $task = InternalAPIV2Service::post('/task/test_status/' . $name, $testData);
            return $this->resObjectGet($task, 'task', $request->path());
        }

        $params['task_run_log_id'] = (int)$params['task_run_log_id'];

        // 真实采集数据接口
        Log::debug('[v2 DataController converter] batchHandle params = ', $params);
        $httpService = new HttpService();
        $resParams = $httpService->post(config('url.jinse_open_url') . '/v2/data/results/handle', $params);
        return $this->resObjectGet($resParams, 'data', $request->path());

    }

    protected function __alarm($taskRunLogId)
    {
        $alarmParams = [
            'task_run_log_id' => $taskRunLogId,
            'content' => '脚本未抓取到数据。'
        ];
        $httpService = new HttpService();
        $httpService->post(config('url.jinse_open_url') . '/v2/alarm_result/handle', $alarmParams);
        return true;
    }
}