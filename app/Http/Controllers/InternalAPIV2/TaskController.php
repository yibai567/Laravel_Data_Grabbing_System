<?php
/**
 * TaskController
 * 任务控制器
 * @author liqi@jinse.com
 * @version 1.0
 * Date: 2018/05/25
 */

namespace App\Http\Controllers\InternalAPIV2;

use App\Models\V2\Task;
use App\Models\V2\Script;
use App\Models\V2\TaskStatistics;
use App\Services\AMQPService;
use App\Services\ValidatorService;
use App\Events\StatisticsEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Log;

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
            'script_id'   => 'required|integer|max:1000',
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
            'script_id'           => $script->id,
            'name'                => $script->name,
            'description'         => $script->description,
            'list_url'            => $script->list_url,
            'data_type'           => $script->data_type,
            'script_path'         => $params['script_path'],
            'is_proxy'            => $script->is_proxy,
            'projects'            => $script->projects,
            'filters'             => $script->filters,
            'actions'             => $script->actions,
            'cron_type'           => $script->cron_type,
            'ext'                 => $script->ext,
            'requirement_pool_id' => $script->requirement_pool_id,
            'company_id'          => $script->company_id,
            'publisher'           => $params['publisher'],
            'status'              => Task::STATUS_INIT,
        ];
        try {
            DB::beginTransaction();

            $taskInfo = Task::create($taskData);

            $result = $taskInfo->toArray();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('TaskProjectMap create  Exception:' . "\t" . $e->getCode() . "\t" . $e->getMessage());
            throw new \Dingo\Api\Exception\ResourceException("create Task is failed");
        }
        $newData = [
            "type" => TaskStatistics::TYPE_TASK,
            "data" => ["task_id" => $result['id'] ]
        ];
        event(new StatisticsEvent($newData));
        return $this->resObjectGet($result, 'task', $request->path());
    }

    /**
     * update
     * 更新task信息
     *
     * @param $request
     * @return array
     */
    public function update(Request $request)
    {
        $params = $request->all();

        //验证参数
        ValidatorService::check($params, [
            'task_id'   => 'required|integer|max:1000',
            'publisher'   => 'required|string|max:100',
            'script_path' => 'required|string|max:255'
        ]);

        $task = Task::find($params['task_id']);

        if (empty($task)) {
            throw new \Dingo\Api\Exception\ResourceException('$task is not found');
        }

        $script = Script::find($task->script_id);

        if (empty($script)) {
            throw new \Dingo\Api\Exception\ResourceException('$script is not found');
        }

        //整理task数据
        $taskData = [
            'script_id'           => $script->id,
            'name'                => $script->name,
            'description'         => $script->description,
            'list_url'            => $script->list_url,
            'data_type'           => $script->data_type,
            'script_path'         => $params['script_path'],
            'is_proxy'            => $script->is_proxy,
            'projects'            => $script->projects,
            'filters'             => $script->filters,
            'actions'             => $script->actions,
            'cron_type'           => $script->cron_type,
            'ext'                 => $script->ext,
            'requirement_pool_id' => $script->requirement_pool_id,
            'company_id'          => $script->company_id,
            'publisher'           => $params['publisher'],
            'status'              => Task::STATUS_INIT,
        ];

        $task->update($taskData);

        $newData = [
            "type" => TaskStatistics::TYPE_TASK,
            "data" => ["task_id" => $task->id]
        ];
        event(new StatisticsEvent($newData));

        return $this->resObjectGet($task->toArray(), 'task', $request->path());
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
            'id' => 'required|integer|max:3000',
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
            'id' => 'required|integer|max:3000',
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
            'id' => 'required|integer|max:3000',
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
            'id' => 'required|integer|max:3000',
        ]);
        $task = Task::find($params['id']);

        $result = [];

        if (!empty($task)) {
            $result = $task->toArray();
        }

        return $this->resObjectGet($result, 'task', $request->path());
    }

    /**
     * 测试成功
     * @param
     * @return array
     */
    public function updateTestStatusSuccess(Request $request)
    {
        $params = $request->all();

        //检测参数
        ValidatorService::check($params, [
            'task_id' => 'required|integer',
            'task_result' => 'required|array'
        ]);
        $task = Task::find($params['task_id']);

        //判断task数据是否存在
        if (empty($task)) {
            throw new \Dingo\Api\Exception\ResourceException('$task is not found');
        }

        $task->last_test_end_at = date('Y-m-d H:i:s');
        $task->test_status = Task::TEST_STATUS_SUCCESS;
        $task->test_result = $params['task_result'];

        $task->save();


        $result = $task->toArray();

        return $this->resObjectGet($result, 'task', $request->path());
    }

    /**
     * 测试失败
     * @param
     * @return array
     */
    public function updateTestStatusFail(Request $request)
    {
        $params = $request->all();

        //检测参数
        ValidatorService::check($params, [
            'task_id' => 'required|integer',
            'task_result' => 'required|array'
        ]);
        $task = Task::find($params['task_id']);

        //判断task数据是否存在
        if (empty($task)) {
            throw new \Dingo\Api\Exception\ResourceException('$task is not found');
        }

        $task->last_test_end_at = date('Y-m-d H:i:s');
        $task->test_status = Task::TEST_STATUS_FAIL;
        $task->test_result = $params['task_result'];

        $task->save();


        $result = $task->toArray();

        return $this->resObjectGet($result, 'task', $request->path());
    }

    /**
     * 测试
     * @param
     * @return array
     */
    public function updateTestUrl(Request $request)
    {
        $params = $request->all();

        //检测参数
        ValidatorService::check($params, [
            'task_id' => 'required|integer',
            'test_url' => 'required|string'
        ]);
        $task = Task::find($params['task_id']);

        //判断task数据是否存在
        if (empty($task)) {
            throw new \Dingo\Api\Exception\ResourceException('$task is not found');
        }

        $task->last_test_start_at = date('Y-m-d H:i:s');
        $task->test_url = $params['test_url'];

        $task->save();

        $result = $task->toArray();

        //入队列
        $this->__testQueueHandle($result);

        return $this->resObjectGet($result, 'task', $request->path());
    }

    /**
     * 入测试队列
     * @param $task
     * @return bool
     */
    private function __testQueueHandle($task)
    {
        switch ($task['data_type']) {
            case Task::DATA_TYPE_CASPERJS:
                $queue = 'test_engine_casperjs';
                break;

            case Task::DATA_TYPE_HTML:
                $queue = 'test_engine_node';
                break;

            case Task::DATA_TYPE_API:
                $queue = 'test_engine_node';
                break;

            default:
                $queue = '';
                break;
        }

        $params = [
            'path' => $task['script_path'],
            'task_run_log_id' => '0|' . $task['id'],
            'url' => $task['cron_type'] == Task::CRON_TYPE_KEEP_ONCE ? $task['test_url'] : '',
        ];

        $option = [
            'server' => [
                'vhost' => 'crawl',
            ],
            'type' => 'direct',
            'exchange' => 'instant_task',
            'queue' => $queue,
            'name' => $queue
        ];
        try {

            $rmq = AMQPService::getInstance($option);
            $rmq->prepareExchange();
            $rmq->prepareQueue();
            $rmq->queueBind();
            $rmq->publishFormart($params, $queue);
            $rmq->close();
        } catch (\Exception $e) {
            Log::error('[testQueueHandle error]:'."\t".$e->getCode()."\t".$e->getMessage());
        }

        Log::debug('[testQueueHandle] ------- end -------');
        return true;
    }
}