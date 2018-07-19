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

            return $this->resObjectGet(false, 'data', $request->path());
        }


        return $this->resObjectGet(true, 'data', $request->path());
    }

}