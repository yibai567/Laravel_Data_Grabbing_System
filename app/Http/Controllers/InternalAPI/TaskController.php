<?php
/**
 * TaskController
 * 任务控制器
 * @author liqi@jinse.com
 * @version 1.0
 * Date: 2018/05/25
 */

namespace App\Http\Controllers\InternalAPI;

use App\Models\Task;
use App\Services\ValidatorService;
use Illuminate\Http\Request;

class TaskController extends Controller
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
        $task = Task::find($params['id']);

        $result = [];

        if (!empty($task)) {
            $result = $task->toArray();
        }

        return $this->resObjectGet($result, 'task', $request->path());
    }
}