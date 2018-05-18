<?php
/**
 * TaskStatisticsController
 * 任务记录控制器
 * @author liqi@jinse.com
 * @version 1.0
 * Date: 2018/05/17
 */

namespace App\Http\Controllers\InternalAPI;

use App\Models\Task;
use Log;
use App\Services\ValidatorService;
use Illuminate\Http\Request;

class TaskStatisticsController extends Controller
{
    /**
     * update
     * 更新脚本记录
     *
     * @param
     * @return array
     */
    public function update(Request $request)
    {
        Log::debug('[internal TaskStatisticsController update] start!');
        $params = $request->all();

        //验证参数
        ValidatorService::check($params, [
            'task_id' => 'required|integer',
        ]);

        try {
            //查看task信息
            $task = Task::where('id',$params['task_id'])->where('status',Task::STATUS_START)->first();

            //检测task是否存在
            if (empty($task)) {
                Log::error('[internal TaskStatisticsController update] task is not found,id = '.$params['task_id']);

                throw new \Dingo\Api\Exception\ResourceException("task is not found");
            }

            //根据一对一关系,查询taskStatistics信息
            $taskStatistics = $task->taskStatistics;

            //检测taskStatistics是否存在
            if (empty($taskStatistics)) {
                Log::error('[internal TaskStatisticsController update] taskStatistics is not found');

                throw new \Dingo\Api\Exception\ResourceException("taskStatistics is not found");
            }

            //更新taskStatistics信息
            $taskStatistics->last_job_at = date('Y-m-d H:i:s');
            $taskStatistics->run_times += 1;
            $taskStatistics->save();

        } catch (\Exception $e) {
            Log::error('Script update    Exception:'."\t".$e->getCode()."\t".$e->getMessage());

            return $this->resError($e->getCode(), $e->getMessage());
        }

        $result = $taskStatistics->toArray();

        return $this->resObjectGet($result, 'task_statistics', $request->path());

    }
}