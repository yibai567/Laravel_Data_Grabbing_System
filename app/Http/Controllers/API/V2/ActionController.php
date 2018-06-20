<?php
/**
 * ActionController
 * action控制器
 * @author liqi@jinse.com
 * @version 1.0
 * Date: 2018/06/19
 */

namespace App\Http\Controllers\API\V2;

use App\Models\V2\TaskRunLog;
use App\Services\InternalAPIService;
use App\Services\ValidatorService;
use Illuminate\Http\Request;
use Log;

class ActionController extends Controller
{
    /**
     * projectResultReport
     * 项目数据上报
     *
     * @param
     * @return boolean
     */
    public function projectResultReport(Request $request)
    {
        Log::debug('[v2 ActionController projectResultReport] start');
        $params = $request->all();
        ValidatorService::check($params, [
            'project_result_id' => 'required|integer',
        ]);

        Log::debug('[v2 ActionController projectResultReport] id = ' . $params['project_result_id']);

        //调取上报数据信息
        $projectResult = InternalAPIService::get('/project_result', ['id'=>$params['project_result_id']]);

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

            Log::debug('[v2 ActionController dataResultReport] reportData = ' , $reportData );

            try {
                //调用上传数据接口
                $result = InternalAPIService::post('/item/result/report', $reportData);
            } catch (\Exception $e) {
                Log::debug('[v2 ActionController projectResultReport] error message = ' . $e->getMessage());

                Log::debug('[v2 ActionController projectResultReport] data report failed');
            }
        }

        return $this->resObjectGet($result, 'action', $request->path());
    }

    /**
     * nextScript
     * 执行下一步脚本
     *
     * @param
     * @return boolean
     */
    public function nextScript(Request $request)
    {
        Log::debug('[v2 ActionController nextScript] start');
        $params = $request->all();

        ValidatorService::check($params, [
            'project_result_id' => 'required|integer',
            'action_id'         => 'required|integer'
        ]);

        Log::debug('[v2 ActionController nextScript] id = ' . $params['project_result_id']);

        //调取上报数据信息
        $projectResult = InternalAPIService::get('/project_result', ['id'=>$params['project_result_id']]);

        $postTaskActionMapData = [];
        $postTaskActionMapData['task_id'] = $projectResult['task_id'];
        $postTaskActionMapData['project_id'] = $projectResult['project_id'];
        $postTaskActionMapData['action_id'] = $params['action_id'];
        //调取task_action_map信息
        $taskActionMaps = InternalAPIService::get('/task/action_map/action_id', $postTaskActionMapData);

        foreach ($taskActionMaps as $taskActionMap) {
            $scriptId = $taskActionMap['script_id'];

            if (empty($scriptId)) {
                Log::debug('[v2 ActionController nextScript] script id is empty');
                return $this->resObjectGet(false, 'task_action_map', $request->path());
            }

            $url = $projectResult['detail_url'];

            //TODO 调用队列
        }

        //TODO 调用事件

        return $this->resObjectGet(true, 'action', $request->path());
    }
}