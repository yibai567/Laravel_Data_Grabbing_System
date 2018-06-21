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
use App\Models\V2\TaskActionMap;
use App\Models\V2\TaskRunLog;
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
            throw new \Dingo\Api\Exception\ResourceException('$projectResult is not found');
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

                Log::debug('[InternalAPIV2 ActionController reportResult] data report failed');
            }
        }

        return $this->resObjectGet($result, 'action', $request->path());
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
            throw new \Dingo\Api\Exception\ResourceException('$projectResult is not found');
        }

        //查询task_action_map信息
        $taskActionMap = TaskActionMap::where('task_id', $projectResult->task_id)
                                      ->where('project_id', $projectResult->project_id)
                                      ->where('action_id', $params['action_id'])
                                      ->first();

        if (empty($taskActionMap)) {
            throw new \Dingo\Api\Exception\ResourceException('$taskActionMap is not found');
        }

        $scriptId = $taskActionMap->script_id;

        $detail_url = $projectResult->detail_url;

        //TODO 调用队列


        return $this->resObjectGet(true, 'action', $request->path());
    }
}