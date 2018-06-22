<?php
/**
 * ScriptController
 * 任务与分发项目控制器
 * @author liqi@jinse.com
 * @version 1.0
 * Date: 2018/06/14
 */

namespace App\Http\Controllers\InternalAPIV2;


use App\Models\V2\Task;
use App\Models\V2\TaskActionMap;
use App\Models\V2\TaskFilterMap;
use App\Models\V2\TaskProjectMap;
use App\Services\ValidatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskProjectMapController extends Controller
{
    /**
     * crete
     * 创建task和project的关系
     *
     * @param $request
     * @return array
     */
    public function create(Request $request)
    {
        $params = $request->all();

        //验证参数
        ValidatorService::check($params, [
            'task_id' => 'required|integer|max:3000',
        ]);

        $task = Task::find($params['task_id']);

        if (empty($task)) {
            throw new \Dingo\Api\Exception\ResourceException('$task is not found');
        }

        try {
            DB::beginTransaction();

            $taskFilterMaps = [];
            $taskProjectMaps = [];
            $taskActionMaps = [];

            if (!empty($task->projects)) {
                //创建task与分发项目的关系
                $taskProjectMap['task_id'] = $params['task_id'];
                foreach ($task->projects as $project) {
                    $taskProjectMap['project_id'] = $project;
                    $taskProjectMaps[] = TaskProjectMap::create($taskProjectMap)->toArray();
                }
                if (!empty($task->filters)) {
                    //创建task与过滤器的关系
                    $taskFilterMap['task_id'] = $params['task_id'];
                    foreach ($task->filters as $projectId => $filters) {
                        if (empty($filters)){
                            continue;
                        }
                        foreach ($filters as $filterId => $filter) {
                            $taskFilterMap['project_id'] = $projectId;
                            $taskFilterMap['filter_id'] = $filterId;
                            $taskFilterMap['params'] = $filter;
                            $taskFilterMaps[] = TaskFilterMap::create($taskFilterMap)->toArray();
                        }
                    }
                }

                if (!empty($task->actions)) {
                    //创建task与action的关系
                    $taskActionMap['task_id'] = $params['task_id'];
                    foreach ($task->actions as $projectId => $actions) {
                        if (empty($actions)) {
                            continue;
                        }

                        foreach ($actions as $actionId => $action) {
                            $taskActionMap['project_id'] = $projectId;
                            $taskActionMap['action_id'] = $actionId;
                            $taskActionMap['params'] = $action;
                            $taskActionMaps[] = TaskActionMap::create($taskActionMap)->toArray();
                        }
                    }
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('TaskProjectMap create  Exception:' . "\t" . $e->getCode() . "\t" . $e->getMessage());
            throw new \Dingo\Api\Exception\ResourceException("create TaskProjectMap is failed");
        }

        $result = [];

        $result['task_project_map'] = $taskProjectMaps;
        $result['task_filter_map'] = $taskFilterMaps;
        $result['task_action_map'] = $taskActionMaps;

        return $this->resObjectGet($result, 'task', $request->path());
    }
}