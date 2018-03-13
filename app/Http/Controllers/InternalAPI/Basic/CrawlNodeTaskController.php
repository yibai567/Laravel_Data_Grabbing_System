<?php

namespace App\Http\Controllers\InternalAPI\Basic;

use App\Models\CrawlNode;
use App\Models\CrawlNodeTask;
use Dompdf\Exception;
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
        infoLog('[create] start.');
        $params = $request->all();
        infoLog('[create] validate.', $params);
        $validator = Validator::make($params, [
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
            errorLog('[create] validate fail.', $errors);
            foreach ($errors->all() as $value) {
                errorLog('[create] validate fail message.', $value);
                return $this->resError(401, $value);
            }
        }
        infoLog('[create] validate end.', $params);
        $data = [
            'node_id' => $params['node_id'],
            'crawl_task_id' => $params['crawl_task_id'],
            'cmd_startup' => $params['cmd_startup'],
            'cmd_stop' => $params['cmd_stop'],
            'log_path' => $params['log_path'],
            'status' => $params['status'],
            'start_on' => $params['start_on'],
            'end_on' => $params['end_on'],
        ];
        infoLog('[create] prepare data.', $data);
        $nodeTask = CrawlNodeTask::create($data);
        infoLog('[create] create success.', $nodeTask);
        return $this->resObjectGet($nodeTask, 'crawl_node_task', $request->path());
    }

    /**
     * 获取已有任务id下所有在运行任务
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStartedTaskByTaskId(Request $request)
    {
        infoLog('[getStartedTaskByTaskId] start.');
        $params = $request->all();
        infoLog('[getStartedTaskByTaskId] validate.', $params);
        $validator = Validator::make($params, [
            'crawl_task_id' => 'integer|nullable',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            errorLog('[getStartedTaskByTaskId] validate fail.', $errors);
            foreach ($errors->all() as $value) {
                errorLog('[getStartedTaskByTaskId] validate fail message.', $value);
                return $this->resError(401, $value);
            }
        }
        infoLog('[getStartedTaskByTaskId] validate end.', $params);
        $crawlTaskId = $params['crawl_task_id'];
        infoLog('[getStartedTaskByTaskId] validate end.', $params);
        $task = CrawlNodeTask::where('status', CrawlNodeTask::IS_STARTUP)->where('crawl_task_id', $crawlTaskId)->first();
        infoLog('[getStartedTaskByTaskId] get startup task.', $params);
        return $this->resObjectGet($task, 'crawl_node_task.list_startuped_task', $request->path());
    }

    /**
     * 更改节点任务状态为停用
     * @param Request $request
     * @return array
     */
    public function stop(Request $request)
    {
        infoLog('[stop] start.');
        $params = $request->all();
        infoLog('[stop] validate.', $params);
        $validator = Validator::make($params, [
            'id' => 'integer|nullable',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            errorLog('[stop] validate fail.', $errors);
            foreach ($errors->all() as $value) {
                errorLog('[stop] validate fail message.', $value);
                return $this->resError(401, $value);
            }
        }
        infoLog('[stop] validate end.', $params);
        try {
            infoLog('[stop] get node task.');
            $nodeTask = CrawlNodeTask::find($params['id']);
            infoLog('[stop] get node task.', $nodeTask);
            if (empty($nodeTask)) {
                infoLog('[stop] node task not exist.');
                throw new Exception('找不到节点信息', 401);
            }
            infoLog('[stop] node task change status.');
            if ($nodeTask['status'] !== CrawlNodeTask::IS_STOP) {
                $nodeTask->status = CrawlNodeTask::IS_STOP;
                if (!$nodeTask->save()) {
                    infoLog('[stop] node task save fail.');
                    throw new Exception('node save fail', 401);
                }
            }
        } catch (Exception $e) {
            return $this->resError($e->getMessage(), $e->getCode());
        }

        infoLog('[stop] end.', $params);
        return $this->resObjectGet($nodeTask, 'crawl_node_task.stop', $request->path());
    }

    /**
     * 更改节点任务状态为启动
     * @param Request $request
     * @return array
     */
    public function start(Request $request)
    {
        infoLog('[start] start.');
        $params = $request->all();
        infoLog('[start] validate.', $params);
        $validator = Validator::make($params, [
            'id' => 'integer|nullable',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            errorLog('[start] validate fail.', $errors);
            foreach ($errors->all() as $value) {
                errorLog('[start] validate fail message.', $value);
                return $this->resError(401, $value);
            }
        }
        infoLog('[start] validate end.');
        try {
            infoLog('[start] get node task.');
            $nodeTask = CrawlNodeTask::find($params['id']);

            if (empty($nodeTask)) {
                infoLog('[start] node task not exist.');
                throw new Exception('task can not found', 401);
            }
            infoLog('[start] node task change status.');
            if ($nodeTask['status'] !== CrawlNodeTask::IS_STARTUP) {
                $nodeTask->status = CrawlNodeTask::IS_STARTUP;
                if (!$nodeTask->save()) {
                    infoLog('[start] node task save fail.');
                    throw new Exception('node save fail', 401);
                }
            }
        } catch (Exception $e) {
            return $this->resError($res['status_code'], $res['message']);
        }
        return $this->resObjectGet($nodeTask, 'crawl_node_task.start', $request->path());
    }
}
