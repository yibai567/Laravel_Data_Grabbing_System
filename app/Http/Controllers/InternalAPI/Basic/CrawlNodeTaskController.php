<?php

namespace App\Http\Controllers\InternalAPI\Basic;

use App\Models\CrawlNode;
use App\Models\CrawlNodeTask;
use Illuminate\Http\Request;
use App\Http\Controllers\InternalAPI\Controller;
use Illuminate\Support\Facades\Validator;

class CrawlNodeTaskController extends Controller
{
    /**
     * 添加节点任务接口
     * @param Request $request
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'node_id' => 'integer|nullable',
            'crawl_task_id' => 'integer|nullable',
            'cmd_startup' => 'string|nullable',
            'cmd_stop' => 'string|nullable',
            'log_path' => 'string|nullable',
            'status' => 'integer|nullable',
            'start_on' => 'datetime|nullable',
            'end_on' => 'datetime|nullable',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                return $this->resError(401, $value);
            }
        }
        $data = [
            'node_id' => $request->node_id,
            'crawl_task_id' => $request->crawl_task_id,
            'cmd_startup' => $request->cmd_startup,
            'cmd_stop' => $request->cmd_stop,
            'log_path' => $request->log_path,
            'status' => $request->status,
            'start_on' => $request->start_on,
            'end_on' => $request->end_on,
        ];

        $nodeTask = CrawlNodeTask::create($data);
        return $this->resObjectGet($nodeTask, 'crawl_node_task', $request->path());
    }

    /**
     * 更改节点任务状态为停用
     * @param Request $request
     * @return array
     */
    public function stopTask(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'integer|nullable',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                return $this->resError(401, $value);
            }
        }
        $data = [
            'id' => intval($request->id),
        ];
        $nodeTask = CrawlNodeTask::find($data['id']);
        if (empty($nodeTask)) {
            return $this->resError(401, '找不到节点信息');
        }
        if ($nodeTask['status'] !== CrawlNodeTask::IS_STOP) {
            $nodeTask->status = CrawlNodeTask::IS_STOP;
            $nodeTask->save();
        }
        return $this->resObjectGet($nodeTask, 'crawl_node_task.stop', $request->path());
    }

    /**
     * 更改节点任务状态为启动
     * @param Request $request
     * @return array
     */
    public function startTask(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'integer|nullable',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                return $this->resError(401, $value);
            }
        }
        $data = [
            'id' => intval($request->id),
        ];

        $node = CrawlNodeTask::find($data['id']);
        if (empty($node)) {
            return $this->resError(401, '找不到节点信息');
        }
        if ($node['status'] !== CrawlNodeTask::IS_STARTUP) {
            $node->status = CrawlNodeTask::IS_STARTUP;
            $node->save();
        }
        return $this->resObjectGet($node, 'crawl_node_task.start', $request->path());
    }

    /**
     * 获取已有任务id下所有在运行任务
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStartupedTaskByTaskId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'crawl_task_id' => 'integer|nullable',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                return $this->resError(401, $value);
            }
        }
        $crawlTaskId = $request->crawl_task_id;
        $task = CrawlNodeTask::where('status', CrawlNodeTask::IS_STARTUP)->where('crawl_task_id', $crawlTaskId)->first();
        return $this->resObjectGet($task, 'crawl_node_task.list_startuped_task', $request->path());
    }

    /**
     * 更改节点任务状态为停用
     * @param Request $request
     * @return array
     */
    public function stop(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'integer|nullable',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                return $this->resError(401, $value);
            }
        }
        $data = [
            'id' => intval($request->id),
        ];
        $nodeTask = CrawlNodeTask::find($data['id']);
        if (empty($nodeTask)) {
            return $this->resError(401, '找不到节点信息');
        }
        if ($nodeTask['status'] !== CrawlNodeTask::IS_STOP) {
            $nodeTask->status = CrawlNodeTask::IS_STOP;
            $nodeTask->save();
        }
        return $this->resObjectGet($nodeTask, 'crawl_node_task.stop', $request->path());
    }

    /**
     * 更改节点任务状态为启动
     * @param Request $request
     * @return array
     */
    public function start(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'integer|nullable',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                return $this->resError(401, $value);
            }
        }
        $data = [
            'id' => intval($request->id),
        ];

        $node = CrawlNodeTask::find($data['id']);
        if (empty($node)) {
            return $this->resError(401, '找不到节点信息');
        }
        if ($node['status'] !== CrawlNodeTask::IS_STARTUP) {
            $node->status = CrawlNodeTask::IS_STARTUP;
            $node->save();
        }
        return $this->resObjectGet($node, 'crawl_node_task.start', $request->path());
    }
}
