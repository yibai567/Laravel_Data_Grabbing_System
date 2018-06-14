<?php
/**
 * TaskController
 * 任务控制器
 * @author liqi@jinse.com
 * @version 1.0
 * Date: 2018/05/25
 */

namespace App\Http\Controllers\InternalAPIV2;

use App\Models\TaskStatistics;
use App\Models\V2\Task;
use App\Models\V2\Script;
use App\Services\InternalAPIService;
use App\Services\ValidatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{

    /**
     * crete
     * 创建task信息
     *
     * @param $request
     * @return array
     */
    public function create(Request $request)
    {
        $params = $request->all();

        //验证参数
        ValidatorService::check($params, [
            'script_id'   => 'required|integer',
            'publisher'   => 'required|string|max:100',
            'script_path' => 'required|string|max:255'
        ]);

        //更改script对应的task为停止
        $script = Script::find($params['script_id']);
        if (empty($script)) {
            throw new \Dingo\Api\Exception\ResourceException('$script is not found');
        }

        //整理task数据
        $taskData = [
            'script_id'   => $script->id,
            'name'        => $script->name,
            'description' => $script->description,
            'list_url'    => $script->list_url,
            'data_type'   => $script->data_type,
            'script_path' => $params['script_path'],
            'is_proxy'    => $script->is_proxy,
            'projects'    => $script->projects,
            'filters'     => $script->filters,
            'actions'     => $script->actions,
            'cron_type'   => $script->cron_type,
            'ext'         => $script->ext,
            'publisher'   => $params['publisher'],
            'status'      => Task::STATUS_INIT,
        ];
        try {
            DB::beginTransaction();

            $taskInfo = Task::create($taskData);

            $result = $taskInfo->toArray();

            //调用保存任务与分发器关系接口
            $postTaskProjectMapData = [];
            $postTaskProjectMapData['task_id'] = $result['id'];

            InternalAPIService::post('/task/project_map', $postTaskProjectMapData);

            $postTaskData['id'] = $result['id'];
            InternalAPIService::post('/task/start', $postTaskData);

            //生成task_statistics
            $taskStatistics = new TaskStatistics;

            $taskStatistics->task_id = $result['id'];

            $taskStatistics->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('TaskProjectMap create  Exception:' . "\t" . $e->getCode() . "\t" . $e->getMessage());
            throw new \Dingo\Api\Exception\ResourceException("create Task is failed");
        }

        return $this->resObjectGet($result, 'task', $request->path());
    }

    /**
     * taskStart
     * 开启任务
     *
     * @param
     * @return array
     */
    public function taskStart(Request $request)
    {
        $params = $request->all();

        //检测参数
        ValidatorService::check($params, [
            'id' => 'required|integer',
        ]);
        $task = Task::find($params['id']);

        //判断task数据是否存在
        if (empty($task)) {
            throw new \Dingo\Api\Exception\ResourceException('$task is not found');
        }

        $task->status = Task::STATUS_START;

        $task->save();

        $result = $task->toArray();

        return $this->resObjectGet($result, 'task', $request->path());
    }

    /**
     * taskStop
     * 停止任务
     *
     * @param
     * @return array
     */
    public function taskStop(Request $request)
    {
        $params = $request->all();

        //检测参数
        ValidatorService::check($params, [
            'id' => 'required|integer',
        ]);
        $task = Task::find($params['id']);

        //判断task数据是否存在
        if (empty($task)) {
            throw new \Dingo\Api\Exception\ResourceException('$task is not found');
        }

        $task->status = Task::STATUS_INIT;

        $task->save();

        $result = $task->toArray();

        return $this->resObjectGet($result, 'task', $request->path());
    }

    /**
     * delete
     * 删除任务
     *
     * @param
     * @return array
     */
    public function delete(Request $request)
    {
        $params = $request->all();

        //检测参数
        ValidatorService::check($params, [
            'id' => 'required|integer',
        ]);
        $task = Task::find($params['id']);

        //判断task数据是否存在
        if (empty($task)) {
            throw new \Dingo\Api\Exception\ResourceException('$task is not found');
        }

        $result = $task->delete();

        return $this->resObjectGet($result, 'task', $request->path());
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
        $task = Task::find($params['id']);

        $result = [];

        if (!empty($task)) {
            $result = $task->toArray();
        }

        return $this->resObjectGet($result, 'task', $request->path());
    }
}