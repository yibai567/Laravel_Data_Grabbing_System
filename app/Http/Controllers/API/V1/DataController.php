<?php
/**
 * DataController
 * Data控制器
 * @author liqi@jinse.com
 * @version 1.0
 * Date: 2018/05/23
 */

namespace App\Http\Controllers\API\V1;

use Log;
use App\Models\TaskRunLog;
use App\Services\InternalAPIService;
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
            'task_run_log_id' => 'nullable|integer',
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
            $taskRunLog = InternalAPIService::post('/task_run_log', $selectTaskRunLogData);
            if (empty($taskRunLog)) {
                Log::debug('[v1/DataController batchHandle]  $taskRunLog is not found,task_run_log_id : ' . $params['task_run_log_id']);
                return $this->resObjectGet(false, 'data', $request->path());
            }

            //查询taskRunLog对应的taskId
            $taskId = $taskRunLog['task_id'];
            $updateTaskStatisticsData['task_id'] = $taskId;
            //记录脚本运行记录
            $result = InternalAPIService::post('/task_statistics/update', $updateTaskStatisticsData);

            if (!$result) {

                Log::debug('[v1/DataController batchHandle] update task statistics is failed,task_id : '.$taskId);
                $updateTaskRunLogData['id'] = $params['task_run_log_id'];
                //更改task_runRunLog状态
                InternalAPIService::post('/task_run_log/status/fail', $updateTaskRunLogData);

                return $this->resObjectGet(false, 'data', $request->path());
            }

            $params['task_id'] = $taskId;
            $data = InternalAPIService::post('/datas',$params);

            $dataNum = count($data);
            $updateTaskRunLogData['result_count'] = $dataNum;
            $updateTaskRunLogData['id'] = $params['task_run_log_id'];
            //更改task_runRunLog状态
            InternalAPIService::post('/task_run_log/status/success', $updateTaskRunLogData);

            if ($dataNum > 0) {
                //TODO 加入队列处理
            }
        } catch (\Exception $e) {
            Log::debug('[v1/DataController batchHandle] error message = ' . $e->getMessage());

            $updateTaskRunLogData['id'] = $params['task_run_log_id'];
            //更改task_runRunLog状态
            InternalAPIService::post('/task_run_log/status/fail', $updateTaskRunLogData);

            return $this->resObjectGet(false, 'data', $request->path());
        }


        return $this->resObjectGet(true, 'data', $request->path());
    }

    /**
     * dataResultReport
     * 数据上报
     *
     * @param
     * @return boolean
     */
    public function dataResultReport(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'ids' => 'required|string',
        ]);

        //调取上报数据信息
        $datas = InternalAPIService::get('/datas/ids', $params);

        $newData = [];
        $postNum = 0;
        //遍历上报数据
        foreach ($datas as $data) {

            if (empty($data['title'])) {
                continue;
            }

            $newData[$postNum]['title'] = $data['title'];

            if (empty($data['task_id'])) {
                $newData[$postNum]['task_id'] = "";
            } else {
                $newData[$postNum]['task_id'] = $data['task_id'];
            }

            if (empty($data['detail_url'])) {
                $newData[$postNum]['url'] = "";
            } else {
                $newData[$postNum]['url'] = $data['detail_url'];
            }

            if (empty($data['show_time'])) {
                $newData[$postNum]['date'] = "";
            } else {
                $newData[$postNum]['date'] = $data['show_time'];
            }

            if (empty($data['images'])) {
                $newData[$postNum]['images'] = [];
            } else {
                $newData[$postNum]['images'] = $data['images'];
            }

            if (empty($data['screenshot'])) {
                $newData[$postNum]['screenshot'] = [];
            } else {
                $newData[$postNum]['screenshot'] = $data['screenshot'];
            }

            $postNum += 1;
        }


        //整理数据
        $params['is_test'] = TaskRunLog::TYPE_TEST;
        $params['result'] = json_encode($newData, JSON_UNESCAPED_UNICODE);

        //调用上传数据接口
        $result = InternalAPIService::post('/item/result/report', $params);

        return $this->resObjectGet($result, 'data', $request->path());
    }
}