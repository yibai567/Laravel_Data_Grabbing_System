<?php
/**
 * TaskRunLogController
 * 任务记录控制器
 * @author huangxingxing@jinse.com
 * @version 1.0
 * Date: 2018/05/017
 */

namespace App\Http\Controllers\InternalAPI;

use App\Models\TaskRunLog;
use Illuminate\Support\Facades\DB;
use Log;
use App\Services\ValidatorService;
use Illuminate\Http\Request;

class TaskRunLogController extends Controller
{
    /**
     * create
     * 创建任务记录
     *
     * @param task_id 任务ID
     * @param start_job_at 开始执行时间
     * @param end_job_at 结束时间
     * @param result_count 结果总数
     * @return array
     */
    public function create(Request $request)
    {
        $params = $request->all();

        Log::debug('[create] 接收参数:', $params);

        ValidatorService::check($params, [
            'task_id' => 'required|integer',
            'start_job_at' => 'nullable|date',
            'end_job_at' => 'nullable|date',
            'result_count' => 'nullable|integer',
        ]);

        $res = TaskRunLog::create($params);

        $result = [];

        if (!empty($res)) {
            $result = $res->toArray();
        }

        return $this->resObjectGet($result, 'task_run_log', $request->path());
    }

}
