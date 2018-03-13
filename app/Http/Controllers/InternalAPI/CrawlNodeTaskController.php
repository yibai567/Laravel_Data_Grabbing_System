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
        $params = $request->all();
        infoLog('[create] start.', $params);
        $validator = Validator::make($params, [
            'crawl_task_id' => 'integer|nullable',
        ]);
        infoLog('[create] params validator start.');
        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                errorLog('[create] params validator fail.', $value);
                return $this->resError(401, $value);
            }
        }
        infoLog('[create] params validator end.');
        $taskId = intval($request->crawl_task_id);
        $params = ['crawl_task_id' => $taskId];
        //停止指定任务id当前在运行的任务
        $dispatcher = app('Dingo\Api\Dispatcher');
        infoLog('[create] request internal/basic/ started start.', $params);
        $res = $dispatcher->get(config('url.jinse_internal_url') . '/internal/basic/crawl/node_task/started', $params);
        if ($res['data']) {
            $item = $res['data'];
            $params = ['id' => $item['id']];
            $dispatcher = app('Dingo\Api\Dispatcher');
            infoLog('[create] request internal/crawl/node_task/stop start.', $params);
            $dispatcher->post(config('url.jinse_base_url') . '/internal/crawl/node_task/stop', $params);
            infoLog('[create] request internal/crawl/node_task/stop end.');
        }
        $dispatcher = app('Dingo\Api\Dispatcher');
        infoLog('[create] request internal/basic/crawl/node/usable.', $params);
        $res = $dispatcher->get(config('url.jinse_internal_url') . '/internal/basic/crawl/node/usable');
        if ($res['status_code'] !== 200) {
            errorLog('[create] request internal/basic/crawl/node/get_usable_node error', $res);
            return $this->resError(401, '没有可用Node节点');
        }
        $node = $res['data'];
        if (empty($node) || empty($node['id'])) {
            errorLog('[create] request internal/basic/crawl/node/get_usable_node error', $node);
            return $this->resError(401, '没有可用Node节点');
        }
        $dispatcher = app('Dingo\Api\Dispatcher');
        infoLog('[create] request internal/basic/crawl/task?id start.', $taskId);
        $res = $dispatcher->get(config('url.jinse_internal_url') . '/internal/basic/crawl/task?id=' . $taskId);
        if ($res['status_code'] === 401 ) {
            infoLog('[create] request internal/basic/crawl/task?id error.', $res);
            return $this->resError(401, '任务不存在');
        }
        $task = $res['data'];
        if (!file_exists($task['script_file'])) {
            infoLog('[create] request internal/basic/crawl/task?id error.', $task);
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
        infoLog('[create] request internal/basic/crawl/node_task start.', $data);
        $res = $dispatcher->post(config('url.jinse_internal_url') . '/internal/basic/crawl/node_task', $data);
        $data = [];
        if ($res['data']) {
            $data = $res['data'];
            //启动任务
            $params = ['id' => $data['id']];
            $dispatcher = app('Dingo\Api\Dispatcher');
            infoLog('[create] request internal/basic/crawl/node_task/start start.', $params);
            $res = $dispatcher->post(config('url.jinse_internal_url') . '/internal/basic/crawl/node_task/start', $params);
            infoLog('[create] request internal/basic/crawl/node_task/start end.', $params);

            // 更新任务状态为启动中
            $params = ['id' => $data['crawl_task_id'], 'status' => CrawlTask::IS_START_UP];
            $dispatcher = app('Dingo\Api\Dispatcher');
            infoLog('[create] request internal/crawl/task/status start.', $params);
            $dispatcher->post(config('url.jinse_base_url') . '/internal/crawl/task/status', $params);
            infoLog('[create] internal/crawl/task/status end.', $params);

        }
        infoLog('[create] end.');
        return $this->resObjectGet($data, 'node_task.create', $request->path());
    }

    /**
     * 启动指定id任务
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function start(Request $request)
    {
        infoLog('[start] start.', $request->all());
        $validator = Validator::make($request->all(), [
            'id' => 'integer|nullable',
        ]);
        infoLog('[start] params validator start.');
        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                errorLog('[start] params validator fail.', $value);
                return $this->resError(401, $value);
            }
        }
        infoLog('[start] params validator end.');
        $params = ['id' => intval($request->id)];

        $dispatcher = app('Dingo\Api\Dispatcher');
        infoLog('[start] request internal/basic/crawl/node_task/start start.', $params);
        $data = $dispatcher->post(config('url.jinse_internal_url') . '/internal/basic/crawl/node_task/start', $params);
        $res = [];
        if ($data['data']) {
            $res = $data['data'];
        }
        infoLog('[start] request internal/basic/crawl/node_task/start end.');
        $command = $res['cmd_startup'];
        exec($command, $result);
        infoLog('[start] end.');
        return $this->resObjectGet($result, 'crawl_node_task.start', $request->path());
    }

    /**
     * 停止指定id任务
     * @param Request $request
     * @return array
     */
    public function stop(Request $request)
    {
        infoLog('[stop] start.', $request->all());
        $validator = Validator::make($request->all(), [
            'id' => 'integer|nullable',
        ]);
        infoLog('[stop] params validator start.');
        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                errorLog('[stop] params validator errors.', $value);
                return $this->resError(401, $value);
            }
        }
        infoLog('[stop] params validator end.');
        $params = ['id' => intval($request->id)];
        $dispatcher = app('Dingo\Api\Dispatcher');
        infoLog('[stop] request internal/basic/crawl/node_task/stop start.', $params);
        $res = $dispatcher->post(config('url.jinse_internal_url') . '/internal/basic/crawl/node_task/stop', $params);
        if ($res['status_code'] === 401) {
            errorLog('[stop] request internal/basic/crawl/node_task/stop errors.', $res);
            return $this->resError(401, '没有可用Node节点');
        }
        if ($res['data']) {
            $res = $res['data'];
        }
        infoLog('[stop] request internal/basic/crawl/node_task/stop end.', $params);
        $command = $res['cmd_startup'];
        exec($command, $result);
        // 更新任务状态为停止
        $params = ['id' => $res['crawl_task_id'], 'status' => CrawlTask::IS_PAUSE];
        infoLog('[stop] request internal/crawl/task/status start.', $params);
        $dispatcher = app('Dingo\Api\Dispatcher');
        $res = $dispatcher->post(config('url.jinse_base_url') . '/internal/crawl/task/status', $params);
        infoLog('[stop] request internal/crawl/task/status end.');
        infoLog('[stop] end.');
        return $this->resObjectGet($result, 'crawl_node_task.start', $request->path());
    }
}
