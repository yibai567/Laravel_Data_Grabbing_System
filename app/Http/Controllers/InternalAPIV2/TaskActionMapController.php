<?php
/**
 * TaskActionMapController
 * 模块控制器
 * @author liqi@jinse.com
 * @version 1.0
 * Date: 2018/06/20
 */

namespace App\Http\Controllers\InternalAPIV2;


use App\Models\V2\TaskActionMap;
use App\Services\ValidatorService;
use Illuminate\Http\Request;

class TaskActionMapController extends Controller
{
    /**
     * getByParams
     * 根据多个条件查数据信息
     *
     * @param
     * @return array
     */
    public function getByParams(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'task_id'    => 'required|integer',
            'project_id' => 'nullable|integer',
            'action_id'  => 'nullable|integer',
        ]);

        $taskActionMap = TaskActionMap::where($params)
                                    ->get();

        $result = [];
        if (!empty($taskActionMap)) {
            $result = $taskActionMap->toArray();
        }

        return $this->resObjectGet($result, 'task_action_map', $request->path());
    }
}