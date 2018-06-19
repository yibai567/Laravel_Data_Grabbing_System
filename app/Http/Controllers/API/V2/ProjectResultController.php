<?php
/**
 * ProjectController
 * 项目控制器
 * @author liqi@jinse.com
 * @version 1.0
 * Date: 2018/06/19
 */

namespace App\Http\Controllers\API\V2;


use App\Services\InternalAPIService;
use App\Services\ValidatorService;
use Illuminate\Http\Request;
use Log;

class ProjectResultController extends Controller
{
    /**
     * messageListCreate
     * 快讯列表项目结果处理
     *
     * @param
     * @return boolean
     */
    public function messageListHandle(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'content_type'    => 'required|integer|between:1,3',
            'company'         => 'required|string|max:20',
            'task_id'         => 'required|integer',
            'project_id'      => 'required|integer',
            'task_run_log_id' => 'required|integer',
            'title'           => 'nullable|string|max:65535',
            'md5_title'       => 'nullable|string|max:50',
            'content'         => 'nullable|string|max:65535',
            'detail_url'      => 'nullable|url|max:65535',
            'show_time'       => 'nullable|string|max:100',
            'author'          => 'nullable|string|max:50',
            'read_count'      => 'nullable|string|max:100',
            'thumbnail'       => 'nullable|string|max:65535',
            'screenshot'      => 'nullable|string|max:65535',
            'img_task_undone' => 'nullable|string',
            'img_task_total'  => 'nullable|string',
            'start_time'      => 'required|date',
            'end_time'        => 'required|date',
            'created_time'    => 'required|integer',
        ]);

        if (empty($params['title']) && empty($params['content'])) {
            return $this->resObjectGet(false, 'project_result', $request->path());
        }

        try {
            $project = InternalAPIService::post('/project_result',$params);

            if (!empty($project)) {
                //TODO 事件
            }
        } catch (\Exception $e) {
            Log::debug('[v2 ProjectController messageDetailCreate] error message = ' . $e->getMessage());
        }


        return $this->resObjectGet(true, 'project_result', $request->path());
    }

    /**
     * messageDetailCreate
     * 快讯详情项目结果处理
     *
     * @param
     * @return boolean
     */
    public function messageDetailHandle(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'content_type'    => 'required|integer|between:1,3',
            'company'         => 'required|string|max:20',
            'task_id'         => 'required|integer',
            'project_id'      => 'required|integer',
            'task_run_log_id' => 'required|integer',
            'title'           => 'nullable|string|max:65535',
            'md5_title'       => 'nullable|string|max:50',
            'content'         => 'nullable|string|max:65535',
            'detail_url'      => 'nullable|url|max:65535',
            'show_time'       => 'nullable|string|max:100',
            'author'          => 'nullable|string|max:50',
            'read_count'      => 'nullable|string|max:100',
            'thumbnail'       => 'nullable|string|max:65535',
            'screenshot'      => 'nullable|string|max:65535',
            'img_task_undone' => 'nullable|string',
            'img_task_total'  => 'nullable|string',
            'start_time'      => 'required|date',
            'end_time'        => 'required|date',
            'created_time'    => 'required|integer',
        ]);

        if (empty($params['title']) && empty($params['content'])) {
            return $this->resObjectGet(false, 'project_result', $request->path());
        }

        try {

            $project = InternalAPIService::post('/project_result',$params);

            if (!empty($project)) {
                //TODO 事件
            }
        } catch (\Exception $e) {
            Log::debug('[v2 ProjectController messageDetailCreate] error message = ' . $e->getMessage());
        }


        return $this->resObjectGet(true, 'project_result', $request->path());
    }

}