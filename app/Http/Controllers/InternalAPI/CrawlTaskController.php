<?php

namespace App\Http\Controllers\InternalAPI;

use App\Http\Requests\CrawlTaskCreateRequest;
use App\Models\CrawlSetting;
use App\Models\CrawlTask;
use Dompdf\Exception;
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
        try{
            infoLog('[create] prepare data.', $data);
            $dispatcher = app('Dingo\Api\Dispatcher');
            $res = $dispatcher->post(config('api.basic_api_base_url') . '/internal/basic/crawl/task', $data);
            infoLog('[create] create task.', $res);
            if ($res['status_code'] !== 200) {
                errorLog($res['message'], $res['status_code']);
                throw new Exception($res['message'], $res['status_code']);
            }
            $result = [];
            if ($res['data']) {
                $task = $res['data'];
            }
            infoLog('[create] create success.', $task);
            $data = ['id'=> $task['id']];
            infoLog('[create] prepare data.', $data);
            $dispatcher = app('Dingo\Api\Dispatcher');
            $res = $dispatcher->post(config('api.basic_api_base_url') . '/internal/crawl/task/script', $data);
            infoLog('[create] create script.', $res);
            if ($res['status_code'] !== 200) {
                errorLog($res['message'], $res['status_code']);
                throw new Exception($res['message'], $res['status_code']);
            }

            $data = ['id'=> $task['id']];
            infoLog('[create] prepare data.', $data);
            $dispatcher = app('Dingo\Api\Dispatcher');
            $res = $dispatcher->post(config('api.basic_api_base_url') . '/internal/crawl/task/preview', $data);
            infoLog('[create] test script.', $res);
            if ($data['status_code'] !== 200) {
                errorLog($res['message'], $res['status_code']);
                throw new Exception($res['message'], $res['status_code']);
            }

            $data = ['id'=> $task['id']];
            infoLog('[create] prepare data.', $data);
            $dispatcher = app('Dingo\Api\Dispatcher');
            $res = $dispatcher->post(config('api.basic_api_base_url') . '/internal/crawl/task/start', $params);
            infoLog('[create] start script.', $res);
            if ($res['status_code'] !== 200) {
                errorLog($res['message'], $res['status_code']);
                throw new Exception($res['message'], $res['status_code']);
            }
            $data = ['id'=> $result['id'], 'status' => CrawlTask::IS_START_UP];
            infoLog('[create] prepare data.', $data);
            $dispatcher = app('Dingo\Api\Dispatcher');
            $res = $dispatcher->post(config('api.basic_api_base_url') . '/internal/crawl/task/status', $params);
            infoLog('[create] start script.', $res);
            if ($res['status_code'] !== 200) {
                errorLog($res['message'], $res['status_code']);
                throw new Exception($res['message'], $res['status_code']);
            }
        }catch(Exception $e){
            return $this->resError($e->getMessage(), $e->getCode());
        }
        return $this->resObjectGet('[create] create success', 'crawl_task', $request->path());
    }

    /**
     * 状态更新
     * @param Request $request
     * @return mixed
     */
    public function updateStatus(Request $request)
    {
        infoLog('[createScript] start.', $request);
        $params = $request->all();
        $validator = Validator::make($params, [
            "id" => "integer|required",
            "status" => "integer|required"
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                return $this->resError(401, $value);
            }
        }
        $taskId = $params['id'];
        $status = $params['status'];
        if ($status == CrawlTask::IS_INIT) {
            // 生成脚本
            $params = ['id' => $taskId];
            $dispatcher = app('Dingo\Api\Dispatcher');
            $dispatcher->post(config('api.basic_api_base_url') . '/internal/crawl/task/generate_script', $params);
        }
        $params = ['id' => $taskId, 'status' => $status];
        $dispatcher = app('Dingo\Api\Dispatcher');
        $data = $dispatcher->post(config('api.basic_api_base_url') . '/internal/basic/crawl/task/status', $params);
        if ($data['status_code'] !== 200) {
            return $this->resError($data['status_code'], 'update fail');
        }
        $res = [];
        if ($data['data']) {
            $res = $data['data'];
        }

        return $this->resObjectGet($res, 'crawl_task.updateStatus', $request->path());
    }

    /**
     * 停止任务
     * @param Request $request
     */
    public function stop(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'integer|required',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                return $this->resError(401, $value);
            }
        }
        $taskId = intval($request->id);
        $params = ['crawl_task_id' => $taskId];
        //停止指定任务id当前在运行的任务
        $dispatcher = app('Dingo\Api\Dispatcher');
        $res = $dispatcher->post(config('api.basic_api_base_url') . '/internal/basic/crawl/node_task/get_startuped_task_by_task_id', $params);
        if ($res['data']) {
            $item = $res['data'];
            $params = ['id' => $item['id']];
            $dispatcher = app('Dingo\Api\Dispatcher');
            $dispatcher->post(config('api.basic_api_base_url') . '/internal/crawl/node_task/stop', $params);
        }
        // 更新任务状态为停止
        $params = ['id' => $taskId, 'status' => CrawlTask::IS_PAUSE];
        $dispatcher = app('Dingo\Api\Dispatcher');
        $res = $dispatcher->post(config('api.basic_api_base_url') . '/internal/crawl/task/status', $params);
        return $this->resObjectGet('task stop success', 'crawl_task.execute', $request->path());
    }


    /**
     * 生成抓取任务脚本文件
     * @param Request $request
     */
    public function createScript(Request $request)
    {
        infoLog('[createScript] start.', $request);
        $params = $request->all();
        infoLog('[createScript] validate.', $params);
        $validator = Validator::make($params, [
            'id' => 'integer|required',
        ]);
        infoLog('[createScript] validate.', $validator);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                infoLog('[createScript] validate fail message.', $value);
                return $this->resError(401, $value);
            }
        }
        infoLog('[createScript] validate end.');

        try {
            $data = [
                'id' => $params['id'],
            ];

            $dispatcher = app('Dingo\Api\Dispatcher');
            $res = $dispatcher->post(config('api.basic_api_base_url') . '/internal/basic/crawl/task/script', $data);
            
            if ($res['status_code'] !== 200) {
                errorLog($res['messsage']);
                throw new Exception($res['messsage'], $res['status_code']);
            }

            //调用基础接口获取任务详情
            $dispatcher = app('Dingo\Api\Dispatcher');
            $res = $dispatcher->get(config('api.basic_api_base_url') . '/internal/basic/crawl/task?id=' . $params['id']);
            if ($res['status_code'] !== 200) {
                errorLog($res['messsage']);
                throw new Exception($res['messsage'], $res['status_code']);
            }
            $task = [];
            if ($res['data']) {
                $task = $res['data'];
            }

            if ($task['setting']['type'] == CrawlSetting::TYPE_CUSTOM) {
                //自定义模版类型
                $content = '';
            } else {
                $content = $task['setting']['content'];
                $content = str_replace('{{{crawl_task_id}}}', $task['id'], $content);
                $content = str_replace('{{{setting_selectors}}}', $task['setting']['selectors'], $content);
                $content = str_replace('{{{setting_keywords}}}', $task['setting']['keywords'], $content);
                $content = str_replace('{{{setting_data_type}}}', $task['setting']['data_type'], $content);
            }

            if (!generateScript($task['script_file'], $content)) {
                return $this->resError(402, 'script generate fail');
            }
        } catch(Exception $e) {
            return $this->resError($res['status_code'], $res['message']);
        }
        return $this->resObjectGet('script generate success', 'crawl_task.generate_script', $request->path());
    }

    /**
     * 执行接口-测试使用
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
        infoLog('[preview] validate.', $validator);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                infoLog('[preview] validate fail message.', $value);
                return $this->resError(401, $value);
            }
        }
        infoLog('[start] validate end.');

        // 获取任务详情
        $dispatcher = app('Dingo\Api\Dispatcher');
        $res = $dispatcher->get(config('api.basic_api_base_url') . '/internal/basic/crawl/task?id=' . $params['id']);
        if ($res['status_code'] !== 200) {
                errorLog($res['messsage']);
                throw new Exception($res['messsage'], $res['status_code']);
        }

        $task = $res['data'];
        if (!file_exists($task['script_file'])) {
            return $this->resError(401, 'script file not exist');
        }
        $command = 'casperjs ' . $task['script_file'] . ' --env=test';
        $output = shell_exec($command);
        return $this->resObjectGet($output, 'crawl_task.execute', $request->path());
    }

    /**
     * 启动任务
     * @param Request $request
     * @return array
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
        try {
            // 获取任务详情
            $dispatcher = app('Dingo\Api\Dispatcher');
            $res = $dispatcher->get(config('api.basic_api_base_url') . '/internal/basic/crawl/task?id=' . $params['id']);
            if ($res['status_code'] !== 200) {
                errorLog($res['messsage']);
                throw new Exception($res['messsage'], $res['status_code']);
            }
            $task = $res['data'];
            //创建节点任务
            $params = ['crawl_task_id' => $task['id']];

            $dispatcher = app('Dingo\Api\Dispatcher');
            $res = $dispatcher->post(config('api.basic_api_base_url') . '/internal/crawl/node_task', $params);
            if ($res['status_code'] !== 200) {
                errorLog($res['messsage']);
                throw new Exception($res['messsage'], $res['status_code']);
            }
        } catch (Exception $e) {
            return $this->resError($res['status_code'], $res['message']);
        }
        infoLog('[start] end.');
        return $this->resObjectGet($task, 'crawl_task.execute', $request->path());
    }
}
