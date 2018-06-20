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
use App\Models\V2\TaskRunLog;
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
            $taskRunLog = InternalAPIService::get('/task_run_log', $selectTaskRunLogData);

            if (empty($taskRunLog)) {
                Log::debug('[v2 DataController batchHandle]  $taskRunLog is not found,task_run_log_id : ' . $params['task_run_log_id']);
                return $this->resObjectGet(false, 'data', $request->path());
            }

            //查询taskRunLog对应的taskId
            $taskId = $taskRunLog['task_id'];
            $updateTaskStatisticsData['task_id'] = $taskId;
            //记录脚本运行记录
            $result = InternalAPIService::post('/task_statistics/update', $updateTaskStatisticsData);

            if (!$result) {

                Log::debug('[v2 DataController batchHandle] update task statistics is failed,task_id : '.$taskId);
                $updateTaskRunLogData['id'] = $params['task_run_log_id'];
                //更改task_runRunLog状态
                InternalAPIService::post('/task_run_log/status/fail', $updateTaskRunLogData);

                return $this->resObjectGet(false, 'data', $request->path());
            }

            $params['task_id'] = $taskId;
            $datas = InternalAPIService::post('/datas',$params);

            $dataNum = count($datas);
            $updateTaskRunLogData['result_count'] = $dataNum;
            $updateTaskRunLogData['id'] = $params['task_run_log_id'];
            //更改task_runRunLog状态
            InternalAPIService::post('/task_run_log/status/success', $updateTaskRunLogData);

            if ($dataNum > 0) {
                event(new SaveDataEvent($datas));
            }
        } catch (\Exception $e) {
            Log::debug('[v2 DataController batchHandle] error message = ' . $e->getMessage());

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
        Log::debug('[v2 DataController dataResultReport] start');
        $params = $request->all();

        ValidatorService::check($params, [
            'ids' => 'required|string',
        ]);
        Log::debug('[v2 DataController dataResultReport] ids = ' . $params['ids']);
        //调取上报数据信息
        $datas = InternalAPIService::get('/datas/ids', $params);

        $newDatas = [];
        //遍历上报数据
        foreach ($datas as $data) {
            $newData = [];
            if (empty($data['title']) || empty($data['task_id'])) {
                continue;
            }

            $newData['title'] = $data['title'];

            $newData['task_id'] = $data['task_id'];

            if (!empty($data['detail_url'])) {
                $newData['url'] = $data['detail_url'];
            }

            if (!empty($data['show_time'])) {
                $newData['date'] = $data['show_time'];
            }

            $newData['images'] = [];
            if (!empty($data['thumbnail'])) {
                $thumbnail = json_decode($data['thumbnail'],true);
                foreach ($thumbnail as $key=>$url) {
                    $getSize = getimagesize($url);
                    $newData['images'][$key]['width'] = $getSize[0];
                    $newData['images'][$key]['height'] = $getSize[1];
                    $newData['images'][$key]['url'] = $url;
                }
            }

            if (!empty($data['screenshot'])) {
                $newData['screenshot'] = $data['screenshot'];
            }

            $newDatas[] = $newData;
        }

        if (!empty($newDatas)) {
            //整理数据
            $reportData['is_test'] = TaskRunLog::TYPE_TEST;

            $reportData['result'] = json_encode($newDatas, JSON_UNESCAPED_UNICODE);

            Log::debug('[v2 DataController dataResultReport] reportData = ' , $reportData );

            try {
                //调用上传数据接口
                $result = InternalAPIService::post('/item/result/report', $reportData);
            } catch (\Exception $e) {
                Log::debug('[v2 DataController dataResultReport] error message = ' . $e->getMessage());

                Log::debug('[v2 DataController dataResultReport] data report failed');
            }
        }

        return $this->resObjectGet($result, 'data', $request->path());
    }
}