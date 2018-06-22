<?php
/**
 * ActionController
 * action控制器
 * @author liqi@jinse.com
 * @version 1.0
 * Date: 2018/06/19
 */

namespace App\Http\Controllers\InternalAPIV2;

use App\Models\V2\ProjectResult;
use App\Models\V2\Task;
use App\Models\V2\TaskActionMap;
use App\Models\V2\TaskRunLog;
use App\Services\AMQPService;
use App\Services\InternalAPIV2Service;
use App\Services\ValidatorService;
use Illuminate\Http\Request;
use Log;

class ActionController extends Controller
{
    /**
     * reportResult
     * 结果上报
     *
     * @param
     * @return boolean
     */
    public function reportResult(Request $request)
    {
        $params = $request->all();
        ValidatorService::check($params, [
            'project_result_id' => 'required|integer|max:999999999',
        ]);

        //获取上报数据信息
        $projectResult = ProjectResult::find($params['project_result_id']);

        if (empty($projectResult)) {
            Log::debug('[InternalAPIv2 ActionController reportResult] $projectResult is not found,project_result_id = ' . $params['project_result_id'] );
            return $this->resObjectGet(false, 'report_result', $request->path());
        }

        $projectResult = $projectResult->toArray();

        //整理上报数据
        $newData = [];

        $newData['title'] = $projectResult['title'];

        $newData['task_id'] = $projectResult['task_id'];

        if (!empty($projectResult['detail_url'])) {
            $newData['url'] = $projectResult['detail_url'];
        }

        if (!empty($projectResult['show_time'])) {
            $newData['date'] = $projectResult['show_time'];
        }

        $newData['images'] = [];
        if (!empty($projectResult['thumbnail'])) {
            $thumbnail = json_decode($projectResult['thumbnail'],true);
            foreach ($thumbnail as $key=>$url) {
                $getSize = getimagesize($url);
                $newData['images'][$key]['width'] = $getSize[0];
                $newData['images'][$key]['height'] = $getSize[1];
                $newData['images'][$key]['url'] = $url;
            }
        }

        if (!empty($projectResult['screenshot'])) {
            $newData['screenshot'] = $projectResult['screenshot'];
        }

        $result = false;

        if (!empty($newData)) {

            //整理数据
            $reportData['is_test'] = TaskRunLog::TYPE_TEST;

            $reportData['result'] = json_encode([$newData], JSON_UNESCAPED_UNICODE);

            Log::debug('[InternalAPIV2 ActionController reportResult] reportData = ' , $reportData );

            try {
                //调用上传数据接口
                $result = InternalAPIV2Service::post('/item/result/report', $reportData);
            } catch (\Exception $e) {
                Log::debug('[InternalAPIV2 ActionController reportResult] error message = ' . $e->getMessage());
            }
        }

        return $this->resObjectGet($result, 'report_result', $request->path());
    }

    /**
     * converterTask
     * 转换任务
     *
     * @param
     * @return boolean
     */
    public function converterTask(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'project_result_id' => 'required|integer|max:999999999',
            'action_id'         => 'required|integer|max:999999999'
        ]);

        //获取上报数据信息
        $projectResult = ProjectResult::find($params['project_result_id']);

        if (empty($projectResult)) {
            Log::debug('[InternalAPIv2 ActionController converterTask] $projectResult is not found,project_result_id = ' . $params['project_result_id'] );
            return $this->resObjectGet(false, 'converter_task', $request->path());
        }

        //查询task_action_map信息
        $taskActionMap = TaskActionMap::where('task_id', $projectResult->task_id)
                                      ->where('project_id', $projectResult->project_id)
                                      ->where('action_id', $params['action_id'])
                                      ->first();

        if (empty($taskActionMap)) {
            Log::debug('[InternalAPIv2 ActionController converterTask] $taskActionMap is not found,task_id = ' . $projectResult->task_id . ',project_id = ' . $projectResult->project_id . ',action_id = ' . $params['action_id']);
            return $this->resObjectGet(false, 'converter_task', $request->path());
        }

        $actionParams = $taskActionMap->params;

        $task_id = $actionParams['task_id'];

        if (empty($task_id)) {
            Log::debug('[InternalAPIv2 ActionController converterTask] the task_id in task_action_map params is not found');
            return $this->resObjectGet(false, 'converter_task', $request->path());
        }

        //查询task信息
        $task = Task::find($task_id);

        if (empty($task)) {
            Log::debug('[InternalAPIv2 ActionController converterTask] $task is not found,task_id = ' . $task_id );
            return $this->resObjectGet(false, 'converter_task', $request->path());
        }

        $script_path = $task->script_path;

        //建立task_run_log数据
        $taskRunLog = InternalAPIV2Service::post('/task_run_log', ['task_id' => $task_id]);

        if (empty($taskRunLog)) {
            Log::debug('[InternalAPIv2 ActionController converterTask] $taskRunLog create is failed,task_id = ' . $task_id );
            return $this->resObjectGet(false, 'converter_task', $request->path());
        }
        $detail_url = $projectResult->detail_url;

        if (empty($detail_url)) {
            Log::debug('[InternalAPIv2 ActionController converterTask] the detail_url in project_result is empty,project_result_id = ' . $params['project_result_id']);
            return $this->resObjectGet(false, 'converter_task', $request->path());
        }

        switch ($task->data_type) {
            case Task::DATA_TYPE_CASPERJS:
                $routingKey = 'casperjs';
                break;

            case Task::DATA_TYPE_HTML:
                $routingKey = 'node';
                break;

            case Task::DATA_TYPE_API:
                $routingKey = 'node';
                break;

            default:
                $routingKey = '';
                break;
        }

        $message = [
            'path'            => $script_path,
            'task_run_log_id' => $taskRunLog['id'],
            'url'             => $detail_url
        ];

        //TODO 调用队列
        $option = [
            'server'   => [
                'vhost' => 'test',
            ],
            'type'     => 1,
            'exchange' => 'instant_task',
            'queue'    => $routingKey
        ];

        $rmq = new AMQPService($option);
        $rmq->prepareExchange();
        $rmq->prepareQueue();
        $rmq->queueBind();
        $rmq->publish(json_encode($message), $routingKey);
        $rmq->close();

        return $this->resObjectGet(true, 'converter_task', $request->path());
    }
}