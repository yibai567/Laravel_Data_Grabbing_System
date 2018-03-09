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
        infoLog('抓取平台任务添加业务API开始', $request);
        $validator = Validator::make($request->all(), [
            'name' => 'string|nullable',
            'description' => 'string|nullable',
            'resource_url' => 'required|string|nullable',
            'cron_type' => 'integer|nullable',
            'selectors' => 'string|nullable',
        ]);
        infoLog('抓取平台任务添加业务API参数验证', $validator);
        if ($validator->fails()) {
            infoLog('抓取平台任务添加业务API参数验证失败', $validator->fails());
            $errors = $validator->errors();
            infoLog('抓取平台任务添加业务API参数验证失败', $errors);
            foreach ($errors->all() as $value) {
                infoLog('抓取平台任务添加业务API参数验证失败', $value);
                return  response($value, 401);
            }
        }
        infoLog('抓取平台任务添加业务API参数验证结束');
        $params = [
            'name' => $request->name,
            'description' => $request->description,
            'resource_url' => $request->resource_url,
            'cron_type' => intval($request->cron_type),
            'selectors' => $request->selectors,
        ];
        infoLog('抓取平台任务添加业务API参数过滤', $params);
        $dispatcher = app('Dingo\Api\Dispatcher');
        $data = $dispatcher->post('internal_api/crawl/task', $params);
        infoLog('抓取平台任务添加业务API调用内部创建任务接口internal_api/crawl/task', $params);
        if ($data['status_code'] == 401) {
            infoLog('抓取平台任务添加业务API调用内部创建任务接口internal_api/crawl/task失败', $data);
            return response('参数错误', 401);
        }
        infoLog('抓取平台任务添加业务API调用内部创建任务接口internal_api/crawl/task完成', $data);
        $result = [];
        if ($data['data']) {
            $result = $data['data'];
        }
        infoLog('抓取平台任务添加业务API完成', $result);
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
            'id' => intval($request->get('id')),
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
            'id' => 'integer|required',
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
        return $this->resObjectGet($data, 'crawl_task.generate_script', $request->path());
    }

    /**
     * 执行脚本接口
     * @param Request $request
     * @return mixed
     */
    public function execute(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'integer|required',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                return response($value, 401);
            }
        }
        $params = [
            'id' => intval($request->get('id')),
        ];
        $dispatcher = app('Dingo\Api\Dispatcher');
        $data = $dispatcher->post('internal_api/crawl/task/execute', $params);
        $res = [];
        if ($data['data']) {
            $res = $data['data'];
        }
        return $this->resObjectGet($res, 'crawl_task.execute', $request->path());
    }

    /**
     * 启动任务
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function startup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'integer|required',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                return response($value, 401);
            }
        }
        $params = [
            'id' => intval($request->get('id')),
        ];
        $dispatcher = app('Dingo\Api\Dispatcher');
        $data = $dispatcher->post('internal_api/crawl/task/startup', $params);
        $res = [];
        if ($data['data']) {
            $res = $data['data'];
        }
        return $this->resObjectGet($res, 'crawl_task.startup', $request->path());
    }
}
