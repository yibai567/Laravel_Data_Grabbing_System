<?php

namespace App\Http\Controllers\InternalAPI;

use App\Events\TaskPreview;
use App\Models\CrawlTask;
use App\Services\APIService;
use Dompdf\Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

class CrawlTaskController extends Controller
{
    /**
     * 创建任务
     * @param Request $request
     * @return array
     */
    public function create(Request $request)
    {
        infoLog('[create] start.');
        $params = $request->all();
        infoLog('[create] validate.', $params);
        $validator = Validator::make($params, [
            'resource_url' => 'required|string',
            'cron_type' => 'integer|nullable',
            'selectors' => 'string|nullable|max:150',
            'is_ajax' => 'integer|nullable',
            'is_login' => 'integer|nullable',
            'is_wall' => 'integer|nullable',
            'is_proxy' => 'integer|nullable',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            errorLog('[create] validate fail.', $errors);
            foreach ($errors->all() as $value) {
                errorLog('[create] validate fail message.', $value);
                return $this->resError(401, $value);
            }
        }

        infoLog('[create] validate end.');
        // if (!empty($params['selectors'])) {
        //     if (!isset($params['selectors']['range'])) {
        //         $params['selectors'] = [];
        //         $params['selectors']['range'] = [];
        //     }
        //     // if (isset($params['selectors']['range'])) {
        //     //     $params['selectors']['range'] = [];
        //     // }
        //     // if (!isset($params['selectors']->content)) {
        //     //     $params['selectors']->content = [];
        //     // }
        //     // if (!isset($params['selectors']->tag)) {
        //     //     $params['selectors']->tag = [];
        //     // }
        // }
        if (empty($params['cron_type'])) {
            $params['cron_type'] = 1;
        }

        if (empty($params['is_login'])) {
            $params['is_login'] = 1;
        }
        if (empty($params['is_ajax'])) {
            $params['is_ajax'] = 1;
        }
        if (empty($params['is_wall'])) {
            $params['is_wall'] = 1;
        }
        if (substr($params['resource_url'], 0, 8) == 'https://') {
            $params['protocol'] = CrawlTask::PROTOCOL_HTTPS;
        } else {
            $params['protocol'] = CrawlTask::PROTOCOL_HTTP;
        }
        if (empty($params['is_proxy'])) {
            $params['is_proxy'] = 2;
        }
        $data = $params;
        infoLog('[create] prepare data.', $data);
        try{
            infoLog('[create] prepare data.', $data);
            $res = APIService::basePost('/internal/basic/crawl/task', $data);
            infoLog('[create] create task.', $res);
            if ($res['status_code'] !== 200) {
                errorLog($res['message'], $res['status_code']);
                throw new Exception($res['message'], $res['status_code']);
            }
            $task = [];
            if ($res['data']) {
                $task = $res['data'];
            }
            infoLog('[create] create success.', $task);

            // 异步触发TaskPreview事件
             event(new TaskPreview($task['id']));

            $result = $task;
        } catch (Exception $e){
            return $this->resError($e->getCode(), $e->getMessage());
        }
        return $this->resObjectGet($result, 'crawl_task', $request->path());
    }

    /**
     * 停止任务
     * @param Request $request
     */
    public function stop(Request $request)
    {
        infoLog('[stop] start.');
        $params = $request->all();
        infoLog('[stop] validate.', $params);
        $validator = Validator::make($params, [
            'id' => 'integer|required',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                infoLog('[stop] validate fail message.', $value);
                return $this->resError(401, $value);
            }
        }
        infoLog('[stop] validate end.');
        try {
            //获取任务详情
            $taskDetail = APIService::baseGet('/internal/basic/crawl/task?id=' . $params['id']);
            if ($taskDetail['status_code'] !== 200 || empty($taskDetail)) {
                errorLog($taskDetail['messsage']);
                throw new Exception($taskDetail['messsage'], $taskDetail['status_code']);
            }
            $taskData = $taskDetail['data'];
            if ($taskData['status'] !== CrawlTask::IS_START_UP) {
                return $this->resError(401, 'task not start!');
            }
            $params = ['id' => $taskData['id'], 'status' => CrawlTask::IS_PAUSE];
            $result = APIService::basePost('/internal/basic/crawl/task/update', $params);
            if ($result['status_code'] !== 200) {
                errorLog($result['messsage']);
                throw new Exception('[stop] ' . $result['status_code'] . 'stop fail', 401);
            }
        } catch (Exception $e) {
            return $this->resError($e->getCode(), $e->getMessage());
        }
        infoLog('[stop] end.');
        return $this->resObjectGet($result, 'crawl_task', $request->path());
    }

    /**
     * 启动任务
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function start(Request $request)
    {
        infoLog('[start] start.');
        $params = $request->all();
        infoLog('[start] validate.', $params);
        $validator = Validator::make($params, [
            'id' => 'integer|required',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                infoLog('[start] validate fail message.', $value);
                return $this->resError(401, $value);
            }
        }
        infoLog('[start] validate end.');
        try {
            //获取任务详情
            $taskDetail = APIService::baseGet('/internal/basic/crawl/task?id=' . $params['id']);
            if ($taskDetail['status_code'] !== 200 || empty($taskDetail)) {
                errorLog($taskDetail['messsage']);
                throw new Exception($taskDetail['messsage'], $taskDetail['status_code']);
            }
            $taskData = $taskDetail['data'];
            if ($taskData['status'] != CrawlTask::IS_TEST_SUCCESS && $taskData['status'] != CrawlTask::IS_PAUSE) {
                return $this->resError(401, 'task status does not allow to start');
            }
            $params = ['id' => $taskData['id'], 'status' => CrawlTask::IS_START_UP];
            $data = APIService::basePost('/internal/basic/crawl/task/update', $params);
            if ($data['status_code'] !== 200) {
                errorLog($data['messsage']);
                throw new Exception('[start] ' . $data['status_code'] . 'start fail', 401);
            }
        } catch (Exception $e) {
            return $this->resError($e->getCode(), $e->getMessage());
        }
        infoLog('[start] end.');
        return $this->resObjectGet($data, 'crawl_task', $request->path());
    }
    /**
     * 任务测试
     * @param Request $request
     * @return 任务测试
     */
    public function test(Request $request)
    {
        infoLog('[start] start.');
        $params = $request->all();
        infoLog('[start] validate.', $params);
        $validator = Validator::make($params, [
            'id' => 'integer|required',
        ]);
        infoLog('[start] validate.', $validator);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                infoLog('[start] validate fail message.', $value);
                return $this->resError(401, $value);
            }
        }
        infoLog('[start] validate end.');
        //获取任务详情
        $taskDetail = APIService::baseGet('/internal/basic/crawl/task?id=' . $params['id']);
        if ($taskDetail['status_code'] !== 200 || empty($taskDetail)) {
            errorLog($taskDetail['messsage']);
            throw new Exception($taskDetail['messsage'], $taskDetail['status_code']);
        }
        $output = 'test fail!';

        $folder = '/keep';
        $status = $taskDetail['data']['status'];
        $protocol = $taskDetail['data']['protocol'];
        $crawlTaskId = $taskDetail['data']['id'];
        if ($protocol == CrawlTask::PROTOCOL_HTTPS) {
            $filePrefix = 'https_';
        } else {
            $filePrefix = 'http_';
        }
        $file = $filePrefix . 'tmp_all_a.js';

        $scriptFile = config('path.jinse_script_path') . $folder . '/' . $file;
        if (file_exists($scriptFile) && $status !== CrawlTask::IS_START_UP) {
            $command = 'casperjs ' . $scriptFile . ' --taskid=' . $crawlTaskId;
            exec($command, $output, $returnvar);
            if ($returnvar == 0) {
                if ($status == CrawlTask::IS_INIT || $status == CrawlTask::IS_TEST_ERROR) {
                    $status = CrawlTask::IS_TEST_SUCCESS;
                }
            } else {
                $status = CrawlTask::IS_TEST_ERROR;
            }
        }
        if (is_array($output)) {
            $output = json_encode($output);
        }
        $params['test_time'] = date('Y-m-d H:i:s');
        $params['status'] = $status;
        $params['test_result'] = $output;
        try {
            $result = APIService::basePost('/internal/basic/crawl/task/update', $params);
            if ($result['status_code'] !== 200) {
                errorLog($result['messsage']);
                throw new Exception('[test] ' . $result['status_code'] . 'stop fail', 401);
            }
        } catch (Exception $e) {
            return $this->resError($e->getCode(), $e->getMessage());
        }
        return $this->resObjectGet($result, 'crawl_task', $request->path());
    }

    /**
     * 根据队列名获取队列数据
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByQueueName(Request $request)
    {
        infoLog('[getByQueueName] start.');
        $params = $request->all();
        infoLog('[getByQueueName] validate start.');
        $validator = Validator::make($params, [
            'name' => 'string|required|max:100',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                infoLog('[getByQueueName] validate fail message.', $value);
                return $this->resError(401, $value);
            }
        }
        infoLog('[getByQueueName] validate end.');
        try {
            Redis::connection('queue');
            $data = [];
            if (Redis::lLen($params['name']) > 0 ) {
                for ($i = 0; $i < 5; $i++) {
                    $value = Redis::rpop($params['name']);
                    if (is_null($value)) {
                        break;
                    }
                    $data[$i] = $value;
                }
            }
        } catch (Exception $e) {
            return $this->resError($e->getCode(), $e->getMessage());
        }
        return $this->resObjectGet($data, 'list', $request->path());
    }

    /**
     * 获取队列名称
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getQueueInfo(Request $request)
    {
        try {
            Redis::connection('queue');
            $data = [];
            $keys = Redis::keys('crawl_task*');
            if (count($keys)) {
                foreach ($keys as $key) {
                    $data[] = ['key' => $key, 'count' => Redis::lLen($key)];
                }
            }
        } catch (Exception $e) {
            return $this->resError($e->getCode(), $e->getMessage());
        }
        return $this->resObjectGet($data, 'list', $request->path());
    }
}
