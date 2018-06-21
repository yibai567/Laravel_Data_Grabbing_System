<?php
/**
 * TaskRunLogController
 * 任务记录控制器
 * @author huangxingxing@jinse.com
 * @version 1.0
 * Date: 2018/05/017
 */

namespace App\Http\Controllers\InternalAPIV2;

use App\Models\V2\TaskRunLog;
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
            'end_job_at' => 'nullable|date',
            'result_count' => 'nullable|integer',
        ]);
        $params['start_job_at'] = date('Y-m-d');
        $res = TaskRunLog::create($params);

        $result = [];

        if (!empty($res)) {
            $result = $res->toArray();
        }

        return $this->resObjectGet($result, 'task_run_log', $request->path());
    }

    /**
     * updateStatusSuccess
     * 修改状态为成功
     *
     * @param id 主键ID
     * @param result_count 结果总数
     * @return array
     */

    public function updateStatusSuccess(Request $request)
    {
        $end_job_at = date('Y-m-d H:i:s');
        $params = $request->all();

        Log::debug('[updateStatusSuccess] 接收参数:', $params);

        ValidatorService::check($params, [
            'id' => 'required|integer',
            'result_count' => 'required|integer',
        ]);

        $taskRunLog = TaskRunLog::find($params['id']);

        if (empty($taskRunLog)) {
            Log::debug('[updateStatusSuccess] task run log not exist id = ' . $params['id']);
            throw new \Dingo\Api\Exception\ResourceException(" task run log not exist");
        }

        $taskRunLog->status = TaskRunLog::STATUS_SUCCESS;
        $taskRunLog->end_job_at = $end_job_at;
        $taskRunLog->result_count = $params['result_count'];
        $taskRunLog->save();
        $res = $taskRunLog->toArray();

        return $this->resObjectGet($res, 'task_run_log', $request->path());

    }

    /**
     * updateStatusFAIL
     * 修改状态为失败
     *
     * @param id 主键
     * @return array
     */

    public function updateStatusFAIL(Request $request)
    {
        $end_job_at = date('Y-m-d H:i:s');
        $params = $request->all();

        Log::debug('[updateStatusFAIL] 接收参数:', $params);

        ValidatorService::check($params, [
            'id' => 'required|integer',
        ]);

        $taskRunLog = TaskRunLog::find($params['id']);
        if (empty($taskRunLog)) {
            Log::debug('[updateStatusFAIL] task run log not exist id = ' . $params['id']);
            throw new \Dingo\Api\Exception\ResourceException(" task run log not exist");
        }

        $taskRunLog->status = TaskRunLog::STATUS_FAIL;
        $taskRunLog->end_job_at = $end_job_at;
        $taskRunLog->save();
        $res = $taskRunLog->toArray();

        return $this->resObjectGet($res, 'task_run_log', $request->path());

    }

    /**
     * retrieve
     * 根据id查数据详情
     *
     * @param id
     * @return array
     */
    public function retrieve(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'id' => 'required|integer',
        ]);

        $id = intval($params['id']);
        $taskRunLog = TaskRunLog::find($id);

        $result = [];
        if (!empty($taskRunLog)) {
            $result = $taskRunLog->toArray();
        }

        return $this->resObjectGet($result, 'task_run_log', $request->path());
    }

}
