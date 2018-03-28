<?php

namespace App\Http\Controllers\InternalAPI;

use App\Models\CrawlTask;
use App\Services\APIService;
use App\Services\ValidatorService;
use Dompdf\Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

class CrawlTaskController extends Controller
{
    /**
     * create
     * 创建任务
     *
     * @param resource_url (任务资源URL)
     * @param cron_type 默认 1可持续的
     * @param selectors (选择器)
     * @param is_ajax (是否是ajax，默认1) 1|不是
     * @param is_login (是否登陆，默认1) 1|不是
     * @param is_wall (是否翻墙，默认1) 1|不是
     * @param is_proxy (是否使用协议，默认1) 1|http,2|https
     * @param resource_type (类型) 1|html,2|json
     * @param header (头信息)
     * @return array
     */
    public function create(Request $request)
    {
        $params = $request->all();
        ValidatorService::check($params, [
            'resource_url' => 'required|string',
            'cron_type' => 'integer|nullable',
            'selectors' => 'nullable',
            'is_ajax' => 'integer|nullable',
            'is_login' => 'integer|nullable',
            'is_wall' => 'integer|nullable',
            'is_proxy' => 'integer|nullable',
            'resource_type' => 'integer|nullable',
            'header' => 'nullablew',
            'api_fileds' => 'nullable',
        ]);
        $params['md5_params'] = md5(json_encode($params, true));
        $taskResult = APIService::baseGet('/internal/basic/crawl/task/search', ['resource_url' => $params['resource_url'], 'md5_params' => $params['md5_params']], 'json');
        if (!empty($taskResult)) {
            returnError(401, '任务已存在', $taskResult);
        }

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

        if (empty($params['resource_type']) && !in_array($params['resource_type'], [CrawlTask::RESOURCE_TYPE_HTML, CrawlTask::RESOURCE_TYPE_JSON])) {
            $params['resource_type'] = CrawlTask::RESOURCE_TYPE_HTML;
        }

        $data = $params;
        try{
            $res = APIService::basePost('/internal/basic/crawl/task', $data, 'json');
            $task = [];
            if ($res) {
                $task = $res;
            }
            // 创建测试
            $this->__toTest($task);

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
        $params = $request->all();
        ValidatorService::check($params, [
            'id' => 'integer|required',
        ]);

        $result = [];
        try {
            //获取任务详情
            $taskDetail = APIService::baseGet('/internal/basic/crawl/task?id=' . $params['id']);
            if (empty($taskDetail)) {
                return $this->resError(401, 'task does not exist!');
            }

            $params = ['id' => $taskDetail['id'], 'status' => CrawlTask::IS_PAUSE];
            $result = APIService::basePost('/internal/basic/crawl/task/update', $params);
        } catch (Exception $e) {
            return $this->resError($e->getCode(), $e->getMessage());
        }

        return $this->resObjectGet($result, 'crawl_task', $request->path());
    }

    /**
     * 启动任务
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function start(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'id' => 'integer|required',
        ]);

        $result = [];
        try {
            //获取任务详情
            $taskDetail = APIService::baseGet('/internal/basic/crawl/task?id=' . $params['id']);

            if (empty($taskDetail)) {
                return $this->resError(401, 'task does not exist');
            }

            if ($taskDetail['status'] != CrawlTask::IS_TEST_SUCCESS && $taskDetail['status'] != CrawlTask::IS_PAUSE) {
                return $this->resError(401, 'task status does not allow to start');
            }

            $params = ['id' => $taskDetail['id'], 'status' => CrawlTask::IS_START_UP];
            $result = APIService::basePost('/internal/basic/crawl/task/update', $params);
        } catch (Exception $e) {
            return $this->resError($e->getCode(), $e->getMessage());
        }

        return $this->resObjectGet($result, 'crawl_task', $request->path());
    }
    /**
     * 任务测试
     * @param Request $request
     * @return 任务测试
     */
    public function test(Request $request)
    {
        $params = $request->all();
        ValidatorService::check($params, [
            'id' => 'integer|required',
        ]);

        //获取任务详情
        $taskDetail = APIService::baseGet('/internal/basic/crawl/task?id=' . $params['id']);

        if (empty($taskDetail)) {
            return $this->resError('task does not exist!');
        }

        $this->__toTest($taskDetail);

        return $this->resObjectGet('测试已提交，请稍后查询结果！', 'crawl_task', $request->path());
    }

    /**
     * 根据队列名获取队列数据
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByQueueName(Request $request)
    {
        $params = $request->all();
        ValidatorService::check($params, [
            'name' => 'string|required|max:100',
        ]);

        try {
            $data = [];

            if (Redis::connection('queue')->lLen($params['name']) > 0 ) {
                for ($i = 0; $i < 5; $i++) {
                    $value = Redis::connection('queue')->rpop($params['name']);

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
            $data = [];
            $keys = Redis::connection('queue')->keys('crawl_task*');

            if (count($keys)) {
                foreach ($keys as $key) {
                    $data[] = ['key' => $key, 'count' => Redis::connection('queue')->lLen($key)];
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
     * @return json
     */
    public function listByIds(Request $request)
    {
        $params = $request->all();
        ValidatorService::check($params, [
            'ids' => 'string|required|max:100',
        ]);

        $data = APIService::baseGet('/internal/basic/crawl/tasks/ids', $params);

        if (!empty($data)) {
            foreach ($data as $task) {
                $item = [];
                $item['task_id'] = $task['id'];
                $item['selector'] = $task['selectors'];
                $item['url'] = $task['resource_url'];
                $item['protocol'] = $task['protocol'];
                $item['type'] = $task['type'];
                $item['header'] = $task['header'];
                $result[] = $item;
            }
        }

        return $this->resObjectGet($result, 'list', $request->path());
    }

    /**
     * update
     * 更新任务详情
     *
     * @param Request $request
     * @return json
     */
    public function update(Request $request)
    {
        $params = $request->all();
        ValidatorService::check($params, [
            'id' => 'required|integer',
            'resource_url' => 'nullable|string',
            'cron_type' => 'integer|nullable',
            'selectors' => 'nullable',
            'is_ajax' => 'integer|nullable',
            'is_login' => 'integer|nullable',
            'is_wall' => 'integer|nullable',
            'is_proxy' => 'integer|nullable',
            'resource_type' => 'integer|nullable',
            'header' => 'nullable',
            'api_fileds' => 'nullable',
        ]);
        //$params['md5_params'] = md5(json_encode($params));
        $task = APIService::baseGet('/internal/basic/crawl/task?id=' . $params['id']);

        if (empty($task)) { // task does not exist
            return $this->resError(401, 'task not exist!');
        }

        if ($task['status'] == CrawlTask::IS_START_UP) { // task is startup
            return $this->resError(401, 'task status does not allow update!');
        }

        if (!empty($params['resource_url'])) {
            if (substr($params['resource_url'], 0, 8) == 'https://') {
                $params['protocol'] = CrawlTask::PROTOCOL_HTTPS;
            } else {
                $params['protocol'] = CrawlTask::PROTOCOL_HTTP;
            }
        }

        if (empty(array_diff($params, $task))) {
            return $this->resError(401, 'task nothing be updated!');
        }

        $data = [];
        $data['status'] = CrawlTask::IS_INIT;
        $data = $params;

        try{
            $res = APIService::basePost('/internal/basic/crawl/task/update', $data, 'json');

            $result = [];
            if (!empty($res)) {
                $result = $res;
            }
            $this->__toTest($result);
        } catch (Exception $e){
            return $this->resError($e->getCode(), $e->getMessage());
        }

        return $this->resObjectGet($result, 'crawl_task', $request->path());
    }


    /**
     * __toTest
     * 准备测试
     *
     * @param $task
     */
    private function __toTest($task)
    {
        $item = [
            'task_id' => $task['id'],
            'url' => $task['resource_url'],
        ];

        switch ($task['resource_type']) {
            case CrawlTask::RESOURCE_TYPE_JSON:

                $item['header'] = $item['header'];
                $item['is_proxy'] = $item['is_proxy'];
                $listName = 'crawl_task_json_test';
                break;

            case CrawlTask::RESOURCE_TYPE_HTML:

                $item['selector'] = $task['selectors'];

                if ($task['protocol'] == CrawlTask::PROTOCOL_HTTPS) {
                    $listName = 'crawl_task_https_test_v2';
                } else {
                    $listName = 'crawl_task_http_test_v2';
                }
                break;

            default:

                $item['selector'] = $task['selectors'];

                if ($task['protocol'] == CrawlTask::PROTOCOL_HTTPS) {
                    $listName = 'crawl_task_https_test';
                } else {
                    $listName = 'crawl_task_http_test';
                }
                break;
        }

        Redis::connection('queue')->lpush($listName, json_encode($item));
        return true;
    }
}
