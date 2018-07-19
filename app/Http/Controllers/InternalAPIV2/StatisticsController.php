<?php
/**
 * TaskStatisticsController
 * 任务记录控制器
 * @author liqi@jinse.com
 * @version 1.0
 * Date: 2018/05/17
 */

namespace App\Http\Controllers\InternalAPIV2;

use App\Models\V2\Task;
use App\Models\V2\TaskRunLog;
use App\Models\V2\Data;
use App\Models\V2\TaskStatistics;
use Log;
use App\Services\ValidatorService;
use App\Services\InternalAPIV2Service;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    /**
     * converter
     * 统计分发
     *
     * @param
     * @return array
     */
    public function converter(Request $request)
    {
        $params = $request->all();
        //验证参数
        ValidatorService::check($params, [
            'type' => 'required|integer',
            'data' => 'nullable|array'
        ]);

        switch ($params['type']) {
            case TaskStatistics::TYPE_TASK:
                $result = InternalAPIV2Service::post('/statistics/task', ["data" => $params['data']]);
                return $this->resObjectGet($result, 'statistics', $request->path());
                break;

            default:
                # code...
                break;
        }
    }

    /**
     * __task
     * 任务统计
     *
     * @param
     * @return array
     */
    public function task(Request $request)
    {
        $params = $request->all();
        //验证参数
        ValidatorService::check($params, [
            'data' => 'nullable|array'
        ]);

        $data = $params['data'];
        try {
            //获取任务信息
            $task = Task::find($data['task_id']);
            if (empty($task)) {
                throw new \Dingo\Api\Exception\ResourceException("task does not exist");
            }

            if (!empty($data['task_run_log_id'])) {
                //获取任务结果总数
                $taskResultTotal = Data::where('task_run_log_id', $data['task_run_log_id'])->count();
            }

            //获取任务统计信息
            $taskStatistics = TaskStatistics::where('task_id', $data['task_id'])->first();
            if (empty($taskStatistics)) {
                $taskStatistics = new TaskStatistics;
                $taskStatistics->task_id = $data['task_id'];
            }

            $taskStatistics->data_type = $task['data_type'];
            $taskStatistics->is_proxy = $task['is_proxy'];
            $taskStatistics->cron_type = $task['cron_type'];
            $taskStatistics->status = $task['status'];

            if (!empty($taskResultTotal)) {
                $taskStatistics->total_result = $taskStatistics->total_result + $taskResultTotal;
            }
            $taskStatistics->last_job_at = date('Y-m-d H:i:s');
            $taskStatistics->save();
        } catch (\Exception $e) {
            Log::error('TaskProjectMap create  Exception:' . "\t" . $e->getCode() . "\t" . $e->getMessage());
            throw new \Dingo\Api\Exception\ResourceException("create Task is failed");
        }
        $result = $taskStatistics->toArray();
        return $this->resObjectGet($result, 'statistics', $request->path());
    }
}