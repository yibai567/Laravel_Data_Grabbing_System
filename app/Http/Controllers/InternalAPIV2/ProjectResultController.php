<?php
/**
 * ProjectResultController
 * 项目结果控制器
 * @author liqi@jinse.com
 * @version 1.0
 * Date: 2018/05/07
 */

namespace App\Http\Controllers\InternalAPIV2;


use App\Models\V2\ProjectResult;
use App\Services\ValidatorService;
use Illuminate\Http\Request;

class ProjectResultController extends Controller
{
    /**
     * create
     * 数据增加
     *
     * @param
     * @return array
     */
    public function create(Request $request)
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

        $project = ProjectResult::create($params);

        $result = [];
        if (!empty($project)) {
            $result = $project->toAarray();
        }

        return $this->resObjectGet($result, 'project_result', $request->path());
    }

    /**
     * retrieve
     * 详情
     *
     * @param
     * @return array
     */
    public function retrieve(Request $request)
    {
        $params = $request->all();

        //检测参数
        ValidatorService::check($params, [
            'id' => 'required|integer',
        ]);

        $projectResult = ProjectResult::find($params['id']);

        $result = [];

        if (!empty($projectResult)) {
            $result = $projectResult->toArray();
        }

        return $this->resObjectGet($result, 'project_result', $request->path());
    }
}