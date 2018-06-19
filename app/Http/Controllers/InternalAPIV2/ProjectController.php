<?php
/**
 * ProjectController
 * 分发项目控制器
 * @author liqi@jinse.com
 * @version 1.0
 * Date: 2018/06/15
 */

namespace App\Http\Controllers\InternalAPIV2;


use App\Models\V2\Project;
use App\Services\ValidatorService;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
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

        $project = Project::find($params['id']);

        $result = [];

        if (!empty($project)) {
            $result = $project->toArray();
        }

        return $this->resObjectGet($result, 'project', $request->path());
    }
}