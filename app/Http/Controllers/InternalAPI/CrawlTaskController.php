<?php

namespace App\Http\Controllers\InternalAPI;

use App\Http\Requests\CrawlTaskCreateRequest;
use App\Models\CrawlSetting;
use App\Models\CrawlTask;
use Illuminate\Http\Request;
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
        $validator = Validator::make($request->all(), [
            'name' => 'string|nullable',
            'description' => 'string|nullable',
            'resource_url' => 'string|nullable',
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
        $data = $dispatcher->post('internal_api/basic/crawl/task', $params);
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
     * 状态更新
     * @param Request $request
     * @return mixed
     */
    public function updateStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id" => "integer|required",
            "status" => "integer|required"
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                return  $this->response->error($value, 401);
            }
        }
        $taskId = intval($request->get('id'));
        $status = intval($request->get('status'));
        if ($status == CrawlTask::IS_INIT) {
            // 生成脚本
            $params = ['id' => $taskId];
            $dispatcher = app('Dingo\Api\Dispatcher');
            $dispatcher->post('internal_api/crawl/task/generate_script', $params);
        }
        $params = ['id' => $taskId, 'status' => $status];
        $dispatcher = app('Dingo\Api\Dispatcher');
        $data = $dispatcher->post('internal_api/basic/crawl/task/status', $params);
        if ($data['status_code'] !== 200) {
            return response('更新失败', $data['status_code']);
        }
        $res = [];
        if ($data['data']) {
            $res = $data['data'];
        }

        return $this->resObjectGet($res, 'crawl_task.updateStatus', $request->path());
    }

    /**
     * 生成抓取任务脚本文件
     * @param Request $request
     */
    public function generateScript(Request $request)
    {
        infoLog('抓取平台生成脚本文件接口启动', $request);
        $validator = Validator::make($request->all(), [
            'id' => 'integer|required',
        ]);
        infoLog('抓取平台生成脚本文件接口参数验证', $validator);

        if ($validator->fails()) {
            infoLog('抓取平台生成脚本文件接口参数验证失败', $validator->fails());
            $errors = $validator->errors();
            infoLog('抓取平台生成脚本文件接口参数验证失败错误信息', $errors);
            foreach ($errors->all() as $value) {
                infoLog('抓取平台生成脚本文件接口参数验证失败错误值', $value);
                return  response($value, 401);
            }
        }
        infoLog('抓取平台生成脚本文件接口参数验证结束');
        $params = [
            'id' => intval($request->get('id')),
        ];

        //调用基础接口获取任务详情
        $dispatcher = app('Dingo\Api\Dispatcher');
        $data = $dispatcher->get('internal_api/basic/crawl/task?id=' . $params['id']);
        if ($data['status_code'] == 401) {
            return response('参数错误', 401);
        }
        if (empty($data['data'])) {
            return response('任务不存在', 401);
        }
        $task = $data['data'];
        $params = ['id' => $params['id']];
        $dispatcher = app('Dingo\Api\Dispatcher');
        $data = $dispatcher->post('internal_api/basic/crawl/task/update_script_file', $params);
        if ($data['status_code'] == 401) {
            return response('更新脚本失败', 401);
        }
        if ($task['setting']['type'] == CrawlSetting::TYPE_CUSTOM) {
            //自定义模版类型
            $content = '';
        } else {
            $content = $task['setting']['content'];
            $content = str_replace('{{{URL}}}', $task['resource_url'], $content);
        }

        if (generateScript($task['script_file'], $content)) {
            return response('脚本生成成功', 200);
        } else {
            return response('脚本生成失败', 402);
        }
        infoLog('抓取平台生成脚本文件接口完成');
    }

    /**
     * 执行接口
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
        // 获取任务详情
        $taskId = intval($request->get('id'));
        $dispatcher = app('Dingo\Api\Dispatcher');
        $data = $dispatcher->get('internal_api/basic/crawl/task?id=' . $taskId);
        if ($data['status_code'] == 401) {
            return response('参数错误', 401);
        }

        $task = $data['data'];
        if (!file_exists($task['script_file'])) {
            return response('脚本文件不存在', 401);
        }
        $command = 'casperjs ' . $task['script_file'] . ' --env=test';
        $output = shell_exec($command);
        return $this->resObjectGet($output, 'crawl_task.execute', $request->path());
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
        // 获取任务详情
        $taskId = intval($request->get('id'));
        $dispatcher = app('Dingo\Api\Dispatcher');
        $data = $dispatcher->get('internal_api/basic/crawl/task?id=' . $taskId);
        if ($data['status_code'] == 401) {
            return response('参数错误', 401);
        }
        $task = $data['data'];

        //创建节点任务
        $params = ['crawl_task_id' => $task['id']];
        $dispatcher = app('Dingo\Api\Dispatcher');
        $dispatcher->post('internal_api/crawl/node_task', $params);
        return $this->resObjectGet($data, 'crawl_task.execute', $request->path());
    }
}
