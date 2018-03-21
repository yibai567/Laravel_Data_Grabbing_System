<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\CrawlTaskCreateRequest;
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
            'description' => 'string|nullable',
            'resource_url' => 'required|string|nullable',
            'cron_type' => 'integer|nullable',
            'selectors' => 'string|nullable',
            'setting_id' => 'integer',
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
            $result = $data['data'];
        }
        infoLog('[create] create end.');
        return $this->resObjectGet($result, 'crawl_task', $request->path());
    }

    /**
     * 更新状态接口
     * @param Request $request
     * @return mixed
     */
    public function updateStatus(Request $request)
    {
        infoLog('[updateStatus] start.');
        $params = $request->all();
        infoLog('[updateStatus] validate.', $params);
        $validator = Validator::make($params, [
            'id' => 'integer|required',
            'status' => 'integer|required',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            errorLog('[updateStatus] validate fail.', $errors);
            foreach ($errors->all() as $value) {
                errorLog('[updateStatus] validate fail message.', $value);
                return $this->resError(401, $value);
            }
        }

        $data = APIService::internalPost('/internal/crawl/task/status', $params);
        infoLog('[updateStatus] edit task.', $data);
        if ($data['status_code'] !== 200) {
            errorLog('[updateStatus] edit task error.');
            return $this->resError($data['status_code'], $data['message']);
        }
        $result = [];
        if ($data['data']) {
            $result = $data['data'];
        }
        infoLog('[updateStatus] end.');
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
        return $this->resObjectGet($result, 'crawl_task.stop', $request->path());
    }

    /**
     * 生成脚本接口
     * @param Request $request
     * @return array
     */
    public function createScript(Request $request)
    {
        infoLog('[createScript] start.');
        $params = $request->all();
        infoLog('[createScript] validate.', $params);
        $validator = Validator::make($params, [
            'id' => 'integer|required',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            errorLog('[createScript] validate fail.', $errors);
            foreach ($errors->all() as $value) {
                errorLog('[createScript] validate fail message.', $value);
                return $this->resError(401, $value);
            }
        }

        infoLog('[createScript] validate end.');
        $data = APIService::internalPost('/internal/crawl/task/script', $params);
        if ($data['status_code'] !== 200) {
            errorLog('[createScript] edit task error.');
            return $this->resError($data['status_code'], $data['message']);
        }

        $result = [];
        if ($data['data']) {
            $result = $data['data'];
        }
        infoLog('[createScript] validate end.');
        return $this->resObjectGet($result, 'crawl_task.generate_script', $request->path());
    }

    /**
     * 执行脚本接口
     * @param Request $request
     * @return mixed
     */
    public function preview(Request $request)
    {
        infoLog('[preview] start.');
        $params = $request->all();
        infoLog('[preview] validate.', $params);
        $validator = Validator::make($params, [
            'id' => 'integer|required',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            errorLog('[preview] validate fail.', $errors);
            foreach ($errors->all() as $value) {
                errorLog('[preview] validate fail message.', $value);
                return $this->resError(401, $value);
            }
        }
        infoLog('[preview] validate end');

        $data = APIService::internalPost('/internal/crawl/task/preview', $params);
        if ($data['status_code'] !== 200) {
            errorLog('[preview] edit task error.');
            return $this->resError($data['status_code'], $data['message']);
        }
        $result = [];
        if ($data['data']) {
            $result = $data['data'];
        }
        infoLog('[preview] validate end.');
        return $this->resObjectGet($result, 'crawl_task.preview', $request->path());
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
        return $this->resObjectGet($result, 'crawl_task.start', $request->path());
    }

    /**
     * 修改抓取返回结果
     *
     */
    public function updateResult(Request $request)
    {
        infoLog('[updateResult] start.');
        $params = $request->all();
        infoLog('[updateResult] validate.', $params);
        $validator = Validator::make($params, [
            'id' => 'integer|required',
            'test_result' => 'nullable',
        ]);
        infoLog('[updateResult] validate.', $validator);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                infoLog('[start] validate fail message.', $value);
                return $this->resError(401, $value);
            }
        }
        infoLog('[updateResult] validate end.');

        $result = [];
        if (empty($params['test_result'])) {
            infoLog('[updateResult] test_result empty.');
            return $this->resObjectGet($result, 'crawl_task.result', $request->path());
        }
        $data = APIService::internalPost('/internal/crawl/task/result', $params);
        if ($data['status_code'] !== 200) {
            errorLog('[updateResult] result task error.');
            return $this->resError($data['status_code'], $data['message']);
        }

        if ($data['data']) {
            $result = $data['data'];
        }
        infoLog('[updateResult] end.');
        return $this->resObjectGet($result, 'crawl_task.result', $request->path());
    }
    /**
     * 任务列表
     * @param Request $request
     * @return 任务列表
     */
    public function all(Request $request)
    {
        infoLog('[all] start.');
        $params = $request->all();
        infoLog('[all] validate.', $params);
        $validator = Validator::make($params, [
            'limit' => 'integer|nullable',
            'offset' => 'integer|nullable',
            'protocol' => 'integer|nullable',
            'cron_type' => 'integer|nullable',
            'task_id' => 'integer|nullable',
            'is_proxy' => 'integer|nullable',
        ]);

        infoLog('[all] validate.', $validator);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                infoLog('[start] validate fail message.', $value);
                return $this->resError(401, $value);
            }
        }
        infoLog('[all] validate end.');
        $data = APIService::internalGet('/internal/crawl/tasks', $params);
        if ($data['status_code'] !== 200) {
            errorLog('[all] result task error.');
            return $this->resError($data['status_code'], $data['message']);
        }

        if ($data['data']) {
            $result = $data['data'];
        }
        infoLog('[all] end.');
        return $this->resObjectGet($result, 'crawl_task.result', $request->path());
    }
    /**
     * 更新任务最后执行时间
     * @param Request $request
     */

    public function updateLastJobAt(Request $request)
    {
        infoLog('[updateLastJobAt] start.');
        $params = $request->all();
        infoLog('[updateLastJobAt] validate start.');
        $validator = Validator::make($params, [
            'id' => 'integer|required',
            'last_job_at' => 'required',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                infoLog('[updateLastJobAt] validate fail message.', $value);
                return $this->resError(401, $value);
            }
        }
        infoLog('[updateLastJobAt] validate end.');
        $data = APIService::internalPost('/internal/crawl/task/last_job_at', $params);
        if ($data['status_code'] !== 200) {
            errorLog('[updateLastJobAt] error.', $data);
            return $this->resError($data['status_code'], $data['message']);
        }

        if ($data['data']) {
            $result = $data['data'];
        }
        return $this->resObjectGet($result, 'crawl_task.updateLastJobAt', $request->path());
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
}
