<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\CrawlTaskCreateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CrawlTaskController extends Controller
{
    /**
     * 创建任务接口
     * @param CrawlTaskCreateRequest $request
     * @return array
     */
    public function create(Request $request)
    {
        infoLog('[create] start.', $request);
        $params = $request->all();
        infoLog('[create] validate.', $params);
        $validator = Validator::make($params, [
            'name' => 'string|nullable',
            'description' => 'string|nullable',
            'resource_url' => 'required|string|nullable',
            'cron_type' => 'integer|nullable',
            'selectors' => 'string|nullable',
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
            'name' => $params['name'],
            'description' => $params['description'],
            'resource_url' => $params['resource_url'],
            'cron_type' => $params['cron_type'],
            'selectors' => $params['selectors'],
        ];

        infoLog('[create] prepare data.', $data);
        $dispatcher = app('Dingo\Api\Dispatcher');
        $task = $dispatcher->post('internal/crawl/task', $data);
        infoLog('[create] create task.', $task);
        if ($task['status_code'] !== 200) {
            errorLog($task['message'], $task['status_code']);
            return $this->resError($task['status_code'], $task['message']);
        }
        infoLog('[create] create task success.', $task);
        $result = [];
        if ($task['data']) {
            $result = $task['data'];
        }
        infoLog('[create] create end.', $result);
        return $this->resObjectGet($result, 'crawl_task', $request->path());
    }

    /**
     * 更新状态接口
     * @param Request $request
     * @return mixed
     */
    public function updateStatus(Request $request)
    {
        infoLog('[updateStatus] start.', $request);
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
        infoLog('[updateStatus] validate end.', $params);

        $data = [
            'id' => $params['id'],
            'status' => $params['status'],
        ];
        infoLog('[updateStatus] prepare data.', $data);
        $dispatcher = app('Dingo\Api\Dispatcher');
        $task = $dispatcher->post('internal/crawl/task/status', $data);
        infoLog('[updateStatus] edit task.', $data);
        if ($task['status_code'] !== 200) {
            errorLog('[updateStatus] edit task error.');
            return $this->resError($task['status_code'], $task['message']);
        }
        $result = [];
        if ($task['data']) {
            $result = $task['data'];
        }
        infoLog('[updateStatus] end.', $result);
        return $this->resObjectGet($result, 'crawl_task', $request->path());
    }

    /**
     * 停止任务接口
     * @param Request $request
     * @return array
     */
    public function stop(Request $request)
    {
        infoLog('[stop] start.', $request);
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
        infoLog('[stop] validate end.', $params);

        $data = [
            'id' => $params['id'],
        ];
        infoLog('[stop] execute stop base api', $data);
        $dispatcher = app('Dingo\Api\Dispatcher');
        $res = $dispatcher->post('internal/crawl/task/stop', $data);
        infoLog('[stop] execute stop base api back', $res);
        if ($res['status'] !== 200) {
            errorLog('[stop] edit task error.');
            return $this->resError($res['status_code'], $res['message']);
        }
        $result = [];
        if ($res['data']) {
            $result = $res['data'];
        }
        infoLog('[stop] end', $result);
        return $this->resObjectGet($result, 'crawl_task.stop', $request->path());
    }

    /**
     * 生成脚本接口
     * @param Request $request
     * @return array
     */
    public function createScript(Request $request)
    {
        infoLog('[createScript] start.', $request);
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
        infoLog('[createScript] validate end.', $params);
        $data = [
            'id' => $params['id'],
        ];
        $dispatcher = app('Dingo\Api\Dispatcher');
        $res = $dispatcher->post('internal/crawl/task/generate_script', $data);
        if ($res['status_code'] !== 200) {
            errorLog('[createScript] edit task error.');
            return $this->resError($res['status_code'], $res['message']);
        }
        $result = [];
        if ($res['data']) {
            $result = $res['data'];
        }
        infoLog('[createScript] validate end.', $result);
        return $this->resObjectGet($result, 'crawl_task.generate_script', $request->path());
    }

    /**
     * 执行脚本接口
     * @param Request $request
     * @return mixed
     */
    public function preview(Request $request)
    {
        infoLog('[preview] start.', $request);
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
        infoLog('[preview] validate end.', $params);
        $data = [
            'id' => $params['id'],
        ];
        infoLog('[preview] prepare data', $data);
        $dispatcher = app('Dingo\Api\Dispatcher');
        $res = $dispatcher->post('internal/crawl/task/execute', $params);

        if ($res['status_code'] !== 200) {
            errorLog('[preview] edit task error.');
            return $this->resError($res['status_code'], $res['message']);
        }
        $result = [];
        if ($res['data']) {
            $result = $res['data'];
        }
        infoLog('[preview] validate end.', $result);
        return $this->resObjectGet($res, 'crawl_task.execute', $request->path());
    }

    /**
     * 启动任务
     * @param Request $request
     * @return 启动任务
     */
    public function start(Request $request)
    {
        infoLog('[start] start.', $request);
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

        $data = [
            'id' => $params['id'],
        ];
        infoLog('[start] execute base api', $data);
        $dispatcher = app('Dingo\Api\Dispatcher');
        $res = $dispatcher->post('internal/crawl/task/start', $data);
        if ($res['status_code'] !== 200) {
            errorLog('[start] start task error.');
            return $this->resError($res['status_code'], $res['message']);
        }
        $result = [];
        if ($res['data']) {
            $result = $res['data'];
        }
        infoLog('[start] validate end.', $result);
        return $this->resObjectGet($res, 'crawl_task.startup', $request->path());
    }
}
