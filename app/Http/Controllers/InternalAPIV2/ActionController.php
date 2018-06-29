<?php
/**
 * ActionController
 * action控制器
 * @author liqi@jinse.com
 * @version 1.0
 * Date: 2018/06/19
 */

namespace App\Http\Controllers\InternalAPIV2;

use App\Events\ConverterTaskEvent;
use App\Models\V2\ProjectResult;
use App\Models\V2\TaskActionMap;
use App\Models\V2\TaskRunLog;
use App\Models\V2\Task;
use App\Models\BlockNews;
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
        switch ($projectResult['project_id']) {
            case '1':
                $result = $this->__formatReportResult($projectResult, $request);
                return $this->resObjectGet($result, 'report_result', $request->path());
                break;
            case '2':
                $result = $this->__formatReportResult($projectResult, $request);
                return $this->resObjectGet($result, 'report_result', $request->path());
                break;
            case '3':
                $task = Task::find($projectResult['task_id']);
                if (empty($task))
                {
                    Log::debug('[InternalAPIv2 ActionController reportBlockNews] task is not found' );
                    return $this->resObjectGet(false, 'report_block_news', $request->path());
                }
                try {
                    $blockNews = new BlockNews();
                    $blockNews->requirement_id = $task->requirement_pool_id;
                    $blockNews->list_url = $task->list_url;
                    $blockNews->title = $projectResult['title'];
                    $blockNews->content = $projectResult['content'];
                    $blockNews->read_count = $projectResult['read_count'];
                    $blockNews->detail_url = $task['detail_url'];
                    $blockNews->show_time = $projectResult['show_time'];
                    $result = $blockNews->save();
                    return $this->resObjectGet($result, 'report_result', $request->path());
                } catch (\Exception $e) {
                    Log::debug('[InternalAPIV2 ActionController block_news reportResult] error message = ' . $e->getMessage());
                    return $this->resObjectGet($result, 'report_result', $request->path());
                }
                break;
            default:
                break;
        }
    }

    private function __formatReportResult($projectResult, $request) {
        //整理上报数据
        $newData = [];

        $newData['title'] = $projectResult['title'];

        $newData['task_id'] = $projectResult['task_id'];

        if (!empty($projectResult['detail_url'])) {
            $newData['url'] = $projectResult['detail_url'];
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
                return $this->resObjectGet($result, 'report_result', $request->path());
            }
        }
        return $result;
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
            'project_result_id'  => 'required|integer|max:999999999',
            'task_action_map_id' => 'required|integer|max:999999999'
        ]);

        //获取上报数据信息
        $projectResult = ProjectResult::find($params['project_result_id']);

        if (empty($projectResult)) {
            Log::debug('[InternalAPIv2 ActionController converterTask] $projectResult is not found,project_result_id = ' . $params['project_result_id'] );
            return $this->resObjectGet(false, 'converter_task', $request->path());
        }

        //查询task_action_map信息
        $taskActionMap = TaskActionMap::find($params['task_action_map_id']);

        if (empty($taskActionMap)) {
            Log::debug('[InternalAPIv2 ActionController converterTask] $taskActionMap is not found,task_action_map_id = ' . $params['task_action_map_id']);
            return $this->resObjectGet(false, 'converter_task', $request->path());
        }

        $actionParams = $taskActionMap->params;

        $task_id = $actionParams['task_id'];

        if (empty($task_id)) {
            Log::debug('[InternalAPIv2 ActionController converterTask] the task_id in task_action_map params is not found');
            return $this->resObjectGet(false, 'converter_task', $request->path());
        }

        $data['task_id'] = $task_id;
        $data['detail_url'] = $projectResult->detail_url;

        if (empty($data['detail_url'])) {
            Log::debug('[InternalAPIv2 ActionController converterTask] the detail_url in project_result is empty,project_result_id = ' . $params['project_result_id']);
            return $this->resObjectGet(false, 'converter_task', $request->path());
        }

        try {
            //触发转换task的事件
            event(new ConverterTaskEvent($data));
        } catch (\Exception $e) {
            Log::debug('[InternalAPIV2 ActionController converterTask] error message = ' . $e->getMessage());
            return $this->resObjectGet(false, 'converter_task', $request->path());
        }

        return $this->resObjectGet(true, 'converter_task', $request->path());
    }
}