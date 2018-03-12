<?php

namespace App\Http\Controllers\InternalAPI;

use App\Models\CrawlNodeTask;
use App\Models\CrawlTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CrawlNodeTaskController extends Controller
{
    /**
     * 创建节点任务
     * @param Request $request
     * @return array
     */
    public function create(Request $request)
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
        $taskId = intval($request->crawl_task_id);
        $params = ['crawl_task_id' => $taskId];
        //停止指定任务id当前在运行的任务
        $dispatcher = app('Dingo\Api\Dispatcher');
        $res = $dispatcher->post('internal/basic/crawl/node_task/get_startuped_task_by_task_id', $params);
        if ($res['data']) {
            $item = $res['data'];
            $params = ['id' => $item['id']];
            $dispatcher = app('Dingo\Api\Dispatcher');
            $dispatcher->post('internal/crawl/node_task/stop', $params);
        }
        $dispatcher = app('Dingo\Api\Dispatcher');
        $res = $dispatcher->get('internal/basic/crawl/node/get_usable_node');
        if ($res['status_code'] === 401) {
            return $this->resError(401, '没有可用Node节点');
        }
        $node = $res['data'];
        if (empty($node) && empty($node['id'])) {
            return $this->resError(401, '没有可用Node节点');
        }
        $dispatcher = app('Dingo\Api\Dispatcher');
        $res = $dispatcher->get('internal/basic/crawl/task?id=' . $taskId);
        if ($res['status_code'] === 401 ) {
            return $this->resError(401, '任务不存在');
        }
        $task = $res['data'];
        if (!file_exists($task['script_file'])) {
            return $this->resError(401, '脚本文件不存在');
        }
        $scriptFile = $task['script_file'];
        $cmdStartup = 'docker ' . $scriptFile . ' --ip=' . $node['ip'] . ' start';
        $cmdStop = 'docker ' . $scriptFile . ' --ip=' . $node['ip'] . ' stop';
        $logPath = '/alidata/logs/crawl/';
        $data = [
            'node_id' => $node['id'],
            'crawl_task_id' => $taskId,
            'cmd_startup' => $cmdStartup,
            'cmd_stop' => $cmdStop,
            'log_path' => $logPath,
            'status' => CrawlNodeTask::IS_STARTUP,
            'start_on' => null,
            'end_on' => null,
        ];
        //创建节点任务
        $dispatcher = app('Dingo\Api\Dispatcher');
        $res = $dispatcher->post('internal/basic/crawl/node_task', $data);
        $data = [];
        if ($res['data']) {
            $data = $res['data'];
            //启动任务
            $params = ['id' => $data['id']];
            $dispatcher = app('Dingo\Api\Dispatcher');
            $res = $dispatcher->post('internal/basic/crawl/node_task/start', $params);
            // 更新任务状态为启动中
            $params = ['id' => $data['crawl_task_id'], 'status' => CrawlTask::IS_START_UP];
            $dispatcher = app('Dingo\Api\Dispatcher');
            $dispatcher->post('internal/crawl/task/status', $params);
        }
        return $this->resObjectGet($data, 'node_task.create', $request->path());
    }

    /**
     * 启动指定id任务
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
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
        $params = ['id' => intval($request->id)];
        $dispatcher = app('Dingo\Api\Dispatcher');
        $data = $dispatcher->get('internal/basic/crawl/node_task/start', $params);
        $res = [];
        if ($data['data']) {
            $res = $data['data'];
        }
        $command = $res['cmd_startup'];
        exec($command, $result);
        return $this->resObjectGet($result, 'crawl_node_task.start', $request->path());
    }

    /**
     * 停止指定id任务
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
        $params = ['id' => intval($request->id)];
        $dispatcher = app('Dingo\Api\Dispatcher');
        $res = $dispatcher->post('internal/basic/crawl/node_task/stop', $params);
        if ($res['status_code'] === 401) {
            return $this->resError(401, '没有可用Node节点');
        }
        if ($res['data']) {
            $res = $res['data'];
        }
        $command = $res['cmd_startup'];
        exec($command, $result);
        // 更新任务状态为停止
        $params = ['id' => $res['crawl_task_id'], 'status' => CrawlTask::IS_PAUSE];
        $dispatcher = app('Dingo\Api\Dispatcher');
        $res = $dispatcher->post('internal/crawl/task/status', $params);

        return $this->resObjectGet($result, 'crawl_node_task.start', $request->path());
    }

    /**
     * 启动指定id任务
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
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
        $params = ['id' => intval($request->id)];
        $dispatcher = app('Dingo\Api\Dispatcher');
        $data = $dispatcher->get('internal/basic/crawl/node_task/start', $params);
        $res = [];
        if ($data['data']) {
            $res = $data['data'];
        }
        $command = $res['cmd_startup'];
        exec($command, $result);
        return $this->resObjectGet($result, 'crawl_node_task.start', $request->path());
    }

    /**
     * 停止指定id任务
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
        $params = ['id' => intval($request->id)];
        $dispatcher = app('Dingo\Api\Dispatcher');
        $res = $dispatcher->post('internal/basic/crawl/node_task/stop', $params);
        if ($res['status_code'] === 401) {
            return $this->resError(401, '没有可用Node节点');
        }
        if ($res['data']) {
            $res = $res['data'];
        }
        $command = $res['cmd_startup'];
        exec($command, $result);
        // 更新任务状态为停止
        $params = ['id' => $res['crawl_task_id'], 'status' => CrawlTask::IS_PAUSE];
        $dispatcher = app('Dingo\Api\Dispatcher');
        $res = $dispatcher->post('internal/crawl/task/status', $params);

        return $this->resObjectGet($result, 'crawl_node_task.start', $request->path());
    }
}
