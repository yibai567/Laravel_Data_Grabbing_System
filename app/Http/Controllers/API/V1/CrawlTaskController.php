<?php

namespace App\Http\Controllers\API\V1;

use App\Services\APIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CrawlTaskController extends Controller
{
    /**
     * 创建任务接口
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
        infoLog('[create] validate end.');

        $data = APIService::internalPost('/internal/crawl/task', $params);

        if ($data['status_code'] !== 200) {
            errorLog($data['message'], $data['status_code']);
            return $this->resError($data['status_code'], $data['message']);
        }

        $result = [];
        if ($data['data']) {
            $task = $data['data'];
            $result = ['id'=> $task['id']];
        }

        infoLog('[create] create end.');
        return $this->resObjectGet($result, 'crawl_task', $request->path());
    }
    /**
     * 停止任务接口
     * @param Request $request
     * @return array
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
            errorLog('[stop] validate fail.', $errors);

            foreach ($errors->all() as $value) {
                errorLog('[stop] validate fail message.', $value);
                return $this->resError(401, $value);
            }
        }

        infoLog('[stop] validate end.');
        $data = APIService::internalPost('/internal/crawl/task/stop', $params);
        infoLog('[stop] execute stop internal api back', $data);

        if ($data['status_code'] != 200) {
            errorLog('[stop] edit task error.');
            return $this->resError($data['status_code'], $data['message']);
        }

        $result = [];
        if ($data['data']) {
            $result = $data['data']['data'];
        }

        infoLog('[stop] end');
        return $this->resObjectGet($result, 'crawl_task', $request->path());
    }

    /**
     * 启动任务
     * @param Request $request
     * @return 启动任务
     */
    public function start(Request $request)
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
        $data = APIService::internalPost('/internal/crawl/task/start', $params);

        if ($data['status_code'] !== 200) {
            errorLog('[start] start task error.');
            return $this->resError($data['status_code'], $data['message']);
        }

        $result = [];
        if ($data['data']) {
            $result = $data['data']['data'];
        }

        infoLog('[start] validate end.');
        return $this->resObjectGet($result, 'crawl_task', $request->path());
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
        $data = APIService::internalPost('/internal/crawl/task/test', $params);

        if ($data['status_code'] !== 200) {
            errorLog('[start] start task error.');
            return $this->resError($data['status_code'], $data['message']);
        }

        infoLog('[start] validate end.');
        return $this->resObjectGet('测试提交成功，请稍后查看结果！', 'crawl_task', $request->path());
    }

    /**
     * 根据队列名获取列表数据
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
        $data = APIService::internalGet('/internal/crawl/task/queue/name?name=' . $params['name']);

        if ($data['status_code'] !== 200) {
            errorLog('[updateLastJobAt] error.', $data);
            return $this->resError($data['status_code'], $data['message']);
        }

        $result = [];
        if ($data['data']) {
            $result = $data['data'];
        }

        return $this->resObjectGet($result, 'list', $request->path());
    }

    /**
     * 获取队列信息-名字及任务数
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getQueueInfo(Request $request)
    {
        $data = APIService::internalGet('/internal/crawl/task/queue/info');

        if ($data['status_code'] !== 200) {
            errorLog('[updateLastJobAt] error.', $data);
            return $this->resError($data['status_code'], $data['message']);
        }

        $result = [];
        if ($data['data']) {
            $result = $data['data'];
        }

        return $this->resObjectGet($result, 'list', $request->path());
    }

    /**
     * 根据ids获取任务列表
     * @param Request $request
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
        $data = APIService::internalGet('/internal/crawl/tasks/ids', $params);

        if ($data['status_code'] !== 200) {
            errorLog('[listByIds] error.', $data);
            return $this->resError($data['status_code'], $data['message']);
        }

        $result = [];
        if ($data['data']) {
            $result = $data['data'];
        }

        return $this->resObjectGet($result, 'list', $request->path());
    }

    /**
     * update
     * 更新任务详情
     *
     * @param Request $request
     * @return array
     */
    public function update(Request $request)
    {
        $params = $request->all();
        $validator = Validator::make($params, [
            'id' => 'required|integer',
            'resource_url' => 'nullable|string',
            'cron_type' => 'integer|nullable',
            'selectors' => 'nullable',
            'is_ajax' => 'integer|nullable',
            'is_login' => 'integer|nullable',
            'is_wall' => 'integer|nullable',
            'is_proxy' => 'integer|nullable',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();

            foreach ($errors->all() as $value) {
                errorLog('[create] validate fail message.', $value);
                return $this->resError(401, $value);
            }
        }

        $data = APIService::internalPost('/internal/crawl/task/update', $params);

        if ($data['status_code'] !== 200) {
            errorLog($data['message'], $data['status_code']);
            return $this->resError($data['status_code'], $data['message']);
        }

        $result = [];
        if ($data['data']) {
            $task = $data['data'];
            $result = ['id'=> $task['id']];
        }

        infoLog('[create] create end.');
        return $this->resObjectGet($result, 'crawl_task', $request->path());
    }
}
