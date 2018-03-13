<?php

namespace App\Http\Controllers\InternalAPI;

use App\Models\CrawlNodeTask;
use App\Models\CrawlTask;
use App\Services\APIService;
use Dingo\Api\Facade\API;
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
        infoLog('[create] start.');
        $params = $request->all();
        $validator = Validator::make($params, [
            'crawl_task_id' => 'integer|nullable',
        ]);
        $taskId = $params['crawl_task_id'];
        infoLog('[create] params validator start.');
        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                errorLog('[create] params validator fail.', $value);
                return $this->resError(401, $value);
            }
        }
        infoLog('[create] params validator end.');

        //停止指定任务id当前在运行的任务
        infoLog('[create] request internal/basic/ started start.', $params);
        $data = APIService::internalGet('/internal/basic/crawl/node_task/started', $params);
        if ($data['data']) {
            $item = $data['data'];
            $params = ['id' => $item['id']];
            infoLog('[create] request internal/crawl/node_task/stop start.', $params);
            $res = APIService::basePost('/internal/crawl/node_task/stop', $params);
            if ($res['status_code'] !== 200) {
                errorLog('[create] request internal/crawl/node_task/stop error', $res);
                return $this->resError(401, '没有可用Node节点');
            }
            infoLog('[create] request internal/crawl/node_task/stop end.');
        }

        infoLog('[create] request internal/basic/crawl/node/usable.', $params);
        $res = APIService::baseGet('/internal/basic/crawl/node/usable');
        if ($res['status_code'] !== 200) {
            errorLog('[create] request internal/basic/crawl/node/get_usable_node error', $res);
            return $this->resError(401, '没有可用Node节点');
        }

        $node = $res['data'];
        if (empty($node) || empty($node['id'])) {
            errorLog('[create] request internal/basic/crawl/node/get_usable_node error', $node);
            return $this->resError(401, '没有可用Node节点');
        }

        infoLog('[create] request internal/basic/crawl/task?id start.', $taskId);
        $res = APIService::internalGet('/internal/basic/crawl/task?id=' . $taskId);
        if ($res['status_code'] === 401 ) {
            infoLog('[create] request internal/basic/crawl/task?id error.', $res);
            return $this->resError(401, '任务不存在');
        }
        $task = $res['data'];
        $scriptFile = CrawlTask::SCRIPT_PATH . '/' . $task['script_file'];
        if (!file_exists($scriptFile)) {
            infoLog('[create] request internal/basic/crawl/task?id error.', $task);
            return $this->resError(401, '脚本文件不存在');
        }

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
        infoLog('[create] request internal/basic/crawl/node_task start.', $data);
        $res = APIService::internalPost('/internal/basic/crawl/node_task', $data);
        $data = [];
        if ($res['data']) {
            $data = $res['data'];
            //启动任务
            $params = ['id' => $data['id']];
            infoLog('[create] request internal/basic/crawl/node_task/start start.', $params);
            $res = APIService::internalPost('/internal/basic/crawl/node_task/start', $params);
            infoLog('[create] request internal/basic/crawl/node_task/start end.', $params);
            if ($res['status_code'] !== 200) {
                errorLog($res['message'], $res['status_code']);
                return $this->resError($res['status_code'], $res['message']);
            }

            // 更新任务状态为启动中
            $params = ['id' => $data['crawl_task_id'], 'status' => CrawlTask::IS_START_UP];
            infoLog('[create] request internal/crawl/task/status start.', $params);
            $res = APIService::basePost('/internal/crawl/task/status', $params);
            if ($res['status_code'] !== 200) {
                errorLog($res['message'], $res['status_code']);
                return $this->resError($res['status_code'], $res['message']);
            }
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
        infoLog('[start] start.');
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

        infoLog('[start] request internal/basic/crawl/node_task/start start.', $params);
        $data = APIService::internalPost('/internal/basic/crawl/node_task/start', $params);
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
        infoLog('[stop] start.');
        $params = $request->all();
        $validator = Validator::make($params, [
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

        infoLog('[stop] request internal/basic/crawl/node_task/stop start.', $params);
        $data = APIService::internalPost('/internal/basic/crawl/node_task/stop', $params);
        if ($data['status_code'] !== 200) {
            errorLog($data['message'], $data['status_code']);
            return $this->resError($data['status_code'], $data['message']);
        }
        $res = [];
        if ($data['data']) {
            $res = $data['data'];
        }
        infoLog('[stop] request internal/basic/crawl/node_task/stop end.', $params);

        $command = $res['cmd_startup'];
        exec($command, $result);

        // 更新任务状态为停止
        $params = ['id' => $result['crawl_task_id'], 'status' => CrawlTask::IS_PAUSE];
        infoLog('[stop] request internal/crawl/task/status start.', $params);
        $res = APIService::basePost('/internal/crawl/task/status', $params);
        infoLog('[stop] request internal/crawl/task/status end.');
        infoLog('[stop] end.');
        return $this->resObjectGet($result, 'crawl_node_task.start', $request->path());
    }
}
