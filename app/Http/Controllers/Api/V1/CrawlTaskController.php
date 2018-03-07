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
        $validator = Validator::make($request->all(), [
            'name' => 'string|nullable',
            'description' => 'string|nullable',
            'resource_url' => 'required|string|nullable',
            'cron_type' => 'integer|nullable',
            'selectors' => 'string|nullable',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                return  response($value, 401);
            }
        }
        $params = [
            'name' => $request->name,
            'description' => $request->description,
            'resource_url' => $request->resource_url,
            'cron_type' => intval($request->cron_type),
            'selectors' => $request->selectors,
        ];

        $dispatcher = app('Dingo\Api\Dispatcher');
        $data = $dispatcher->post('internal_api/crawl/task', $params);
        if ($data['status_code'] == 401) {
            return response('参数错误', 401);
        }
        $result = [];
        if ($data['data']) {
            $result = $data['data'];
        }
        return $this->resObjectGet($result, 'crawl_task', $request->path());
    }

    /**
     * 更新状态接口
     * @param Request $request
     * @return mixed
     */
    public function updateStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'integer|required',
            'status' => 'integer|required',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                return  response($value, 401);
            }
        }
        $params = [
            'name' => intval($request->get('name')),
            'status' => intval($request->get('status')),
        ];

        $dispatcher = app('Dingo\Api\Dispatcher');
        $data = $dispatcher->post('internal_api/crawl/task/status', $params);
        if ($data['status_code'] == 401) {
            return response('参数错误', 401);
        }
        $result = [];
        if ($data['data']) {
            $result = $data['data'];
        }
        return $this->resObjectGet($result, 'crawl_task', $request->path());
    }

    /**
     * 生成脚本接口
     * @param Request $request
     * @return array
     */
    public function generateScript(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'string|nullable',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                return  response($value, 401);
            }
        }
        $params = [
            'id' => intval($request->get('id')),
        ];

        $dispatcher = app('Dingo\Api\Dispatcher');
        $data = $dispatcher->post('internal_api/crawl/task/generate_script', $params);

        if ($data['status_code'] == 401) {
            return response('参数错误', 401);
        }
        $result = [];
        if ($data['data']) {
            $result = $data['data'];
        }
        return $this->resObjectGet($result, 'crawl_task.generate_script', $request->path());
    }

    /**
     * 执行脚本接口
     * @param Request $request
     * @return mixed
     */
    public function execute(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'string|nullable',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                return  response($value, 401);
            }
        }
        $params = [
            'id' => intval($request->get('id')),
        ];

        $dispatcher = app('Dingo\Api\Dispatcher');
        $data = $dispatcher->post('internal_api/crawl/task/execute', $params);
        if ($data['status'] == 401) {
            return response('测试失败', 401);
        }
        return $this->resObjectGet('执行成功', 'crawl_task.execute', $request->path());
    }
}
