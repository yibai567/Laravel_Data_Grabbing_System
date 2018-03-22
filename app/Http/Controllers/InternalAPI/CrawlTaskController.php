<?php

namespace App\Http\Controllers\InternalAPI;

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
            'selectors' => 'nullable',
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
        $selectors = ['range' => '','content' => [], 'tags' => []];
        if (is_array($params['selectors'])) {
            if (isset($params['selectors']['range'])) {
                if (mb_strlen($params['selectors']['range']) > 200) {
                    return $this->resError(401, 'range 不能超过200');
                }
                $selectors['range'] = $params['selectors']['range'];
            }
            if (isset($params['selectors']['content'])) {
                $selectors['content'] = $params['selectors']['content'];
            }

            if  (isset($params['selectors']['tags'])) {
                $selectors['tags'] = $params['selectors']['tags'];
            }
        }
        $params['selectors'] = $selectors;
        infoLog('[create] validate end.');

        if (empty($params['cron_type'])) {
            $params['cron_type'] = CrawlTask::CRON_TYPE_KEEP;
        }

        if (empty($params['is_login'])) {
            $params['is_login'] = CrawlTask::IS_LOGIN_FALSE;
        }
        if (empty($params['is_ajax'])) {
            $params['is_ajax'] = CrawlTask::IS_AJAX_FALSE;
        }
        if (empty($params['is_wall'])) {
            $params['is_wall'] = CrawlTask::IS_WALL_FALSE;
        }
        if (substr($params['resource_url'], 0, 8) == 'https://') {
            $params['protocol'] = CrawlTask::PROTOCOL_HTTPS;
        } else {
            $params['protocol'] = CrawlTask::PROTOCOL_HTTP;
        }
        if (empty($params['is_proxy'])) {
            $params['is_proxy'] = CrawlTask::IS_PROXY_FALSE;
        }
        $data = $params;
        infoLog('[create] prepare data.', $data);
        try{
            infoLog('[create] prepare data.', $data);
            $res = APIService::basePost('/internal/basic/crawl/task', $data, 'json');
            infoLog('[create] create task.', $res);
            if ($res['status_code'] !== 200) {
                errorLog($res['message'], $res['status_code']);
                throw new Exception($res['message'], $res['status_code']);
            }
            $task = [];
            if ($res['data']) {
                $task = $res['data'];
            }
            $item = [
                'task_id' => $task['id'],
                'url' => $task['resource_url'],
                'selector' => $task['selectors'],
            ];
            if ($task['protocol'] == CrawlTask::PROTOCOL_HTTPS) {
                $listName = 'crawl_task_https_test';
            } else {
                $listName = 'crawl_task_http_test';
            }
            $result = $task;
            Redis::lpush($listName, json_encode($item));
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
            if (empty($taskData)) {
                return $this->resError(401, 'task does not exist!');
            }
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
            if (empty($taskData)) {
                return $this->resError(401, 'task does not exist');
            }
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
        infoLog('[test] start.');
        $params = $request->all();
        infoLog('[test] validate.', $params);
        $validator = Validator::make($params, [
            'id' => 'integer|required',
        ]);
        infoLog('[test] validate.', $validator);
        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                infoLog('[test] validate fail message.', $value);
                return $this->resError(401, $value);
            }
        }
        infoLog('[test] validate end.');
        //获取任务详情
        $taskDetail = APIService::baseGet('/internal/basic/crawl/task?id=' . $params['id']);
        if ($taskDetail['status_code'] !== 200 || empty($taskDetail)) {
            errorLog($taskDetail['messsage']);
            throw new Exception($taskDetail['messsage'], $taskDetail['status_code']);
        }
        if (empty($taskDetail['data'])) {
            return $this->resError('task does not exist!');
        }
        $task = $taskDetail['data'];
        $item = [
            'task_id'=> $task['id'],
            'url'=> $task['resource_url'],
            'selector'=> $task['selectors']
        ];
        if ($task['protocol'] == CrawlTask::PROTOCOL_HTTPS) {
            $listName = 'crawl_task_https_test';
        } else {
            $listName = 'crawl_task_http_test';
        }
        Redis::lpush($listName, json_encode($item));
        return $this->resObjectGet('测试已提交，请稍后查询结果！', 'crawl_task', $request->path());
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
                    $data[$i] = json_decode($value, true);
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

    /**
     * 根据id列表获取任务列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function listByIds(Request $request)
    {
        infoLog('[listByIds] start.');
        $params = $request->all();
        infoLog('[listByIds] validate start.');
        $validator = Validator::make($params, [
            'ids' => 'string|required|max:100',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                infoLog('[listByIds] validate fail message.', $value);
                return $this->resError(401, $value);
            }
        }
        infoLog('[listByIds] validate end.');
        $data = APIService::baseGet('/internal/basic/crawl/tasks/ids', $params);
        if ($data['status_code'] !== 200) {
            errorLog('[listByIds] error.', $data);
            return $this->resError($data['status_code'], $data['message']);
        }
        $result = [];
        if ($data['data']) {
            $tasks = $data['data'];
            foreach ($tasks as $task) {
                $item = [];
                $item['task_id'] = $task['id'];
                $item['selector'] = $task['selectors'];
                $item['url'] = $task['resource_url'];
                $result[] = $item;
            }
        }
        return $this->resObjectGet($result, 'list', $request->path());
    }
}
