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
        try{
            $dispatcher = app('Dingo\Api\Dispatcher');
            $data = $dispatcher->post('internal/basic/crawl/task', $params);
            if ($data['status_code'] == 401) {
                throw new Exception('参数错误', 401);
            }
            $result = [];
            if ($data['data']) {
                $result = $data['data'];
            }
            $params = ['id'=> $result['id']];
            $dispatcher = app('Dingo\Api\Dispatcher');
            $data = $dispatcher->post('/crawl/task/generate_script', $params);
            if ($data['status_code'] !== 200) {
                throw new Exception('生成脚本失败', 401);
            }

            $params = ['id'=> $result['id']];
            $dispatcher = app('Dingo\Api\Dispatcher');
            $data = $dispatcher->post('/crawl/task/execute', $params);
            if ($data['status_code'] !== 200) {
                throw new Exception('测试失败', 401);
            }

            $params = ['id'=> $result['id']];
            infoLog('抓取平台启动任务接口调用业务基础接口启动任务', $params);
            $dispatcher = app('Dingo\Api\Dispatcher');
            $data = $dispatcher->post('internal/crawl/task/startup', $params);
            if ($data['status_code'] !== 200) {
                throw new Exception('启动失败', 401);
            }
        }catch(Exception $e){
            return $this->resError($e->getMessage(), $e->getCode());
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
                return $this->resError(401, $value);
            }
        }
        $taskId = intval($request->get('id'));
        $status = intval($request->get('status'));
        if ($status == CrawlTask::IS_INIT) {
            // 生成脚本
            $params = ['id' => $taskId];
            $dispatcher = app('Dingo\Api\Dispatcher');
            $dispatcher->post('internal/crawl/task/generate_script', $params);
        }
        $params = ['id' => $taskId, 'status' => $status];
        $dispatcher = app('Dingo\Api\Dispatcher');
        $data = $dispatcher->post('internal/basic/crawl/task/status', $params);
        if ($data['status_code'] !== 200) {
            return $this->resError($data['status_code'], '更新失败');
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
                return $this->resError(401, $value);
            }
        }
        infoLog('抓取平台生成脚本文件接口参数验证结束');
        $params = [
            'id' => intval($request->get('id')),
        ];

        $params = ['id' => $params['id']];
        $dispatcher = app('Dingo\Api\Dispatcher');
        $data = $dispatcher->post('internal/basic/crawl/task/update_script_file', $params);
        if ($data['status_code'] == 401) {
            return $this->resError(401, '更新脚本失败');
        }

        //调用基础接口获取任务详情
        $dispatcher = app('Dingo\Api\Dispatcher');
        $data = $dispatcher->get('internal/basic/crawl/task?id=' . $params['id']);
        if ($data['status_code'] == 401) {
            return $this->resError(401, '参数错误');
        }
        if (empty($data['data'])) {
            return $this->resError(401, '任务不存在');
        }
        $task = $data['data'];
        if ($task['setting']['type'] == CrawlSetting::TYPE_CUSTOM) {
            //自定义模版类型
            $content = '';
        } else {
            $content = $task['setting']['content'];
            $content = str_replace('{{{crawl_task_id}}}', $task['id'], $content);
//            $content = str_replace('{{{task_start_time}}}', '', $content);
//            $content = str_replace('{{{task_end_time}}}', '', $content);
            $content = str_replace('{{{setting_selectors}}}', $task['setting']['selectors'], $content);
            $content = str_replace('{{{setting_keywords}}}', $task['setting']['keywords'], $content);
            $content = str_replace('{{{setting_data_type}}}', $task['setting']['data_type'], $content);
        }

        if (!generateScript($task['script_file'], $content)) {
            return $this->resError(402, '脚本生成失败');
        }
        return $this->resObjectGet('脚本生成成功', 'crawl_task.generate_script', $request->path());
        infoLog('抓取平台生成脚本文件接口完成');
    }

    /**
     * 执行接口-测试使用
     * @return mixed
     */
    public function execute(Request $request)
    {
        infoLog('抓取平台执行接口启动', $request);
        $validator = Validator::make($request->all(), [
            'id' => 'integer|required',
        ]);
        infoLog('抓取平台执行接口参数验证', $validator);

        if ($validator->fails()) {
            infoLog('抓取平台执行接口参数验证失败', $validator->fails());
            $errors = $validator->errors();
            infoLog('抓取平台执行接口参数验证失败错误信息', $errors);
            foreach ($errors->all() as $value) {
                infoLog('抓取平台执行接口参数验证失败错误值', $value);
                return $this->resError(401, $value);
            }
        }
        infoLog('抓取平台生成脚本文件接口参数验证结束');
        // 获取任务详情
        $taskId = intval($request->get('id'));
        $dispatcher = app('Dingo\Api\Dispatcher');
        $data = $dispatcher->get('internal/basic/crawl/task?id=' . $taskId);
        if ($data['status_code'] == 401) {
            return $this->resError(401, '参数错误');
        }
        $task = $data['data'];
        if (!file_exists($task['script_file'])) {
            return $this->resError(401, '脚本文件不存在');
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
    public function startup(Request $request)
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
        // 获取任务详情
        $taskId = intval($request->get('id'));
        $dispatcher = app('Dingo\Api\Dispatcher');
        $data = $dispatcher->get('internal/basic/crawl/task?id=' . $taskId);

        if ($data['status_code'] == 401) {
            return $this->resError(401, '参数错误');
        }
        $task = $data['data'];
        //创建节点任务
        $params = ['crawl_task_id' => $task['id']];
        $dispatcher = app('Dingo\Api\Dispatcher');
        $dispatcher->post('internal/crawl/node_task', $params);
        return $this->resObjectGet($data, 'crawl_task.execute', $request->path());
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
        $res = $dispatcher->post('internal/basic/crawl/node_task/get_startuped_task_by_task_id', $params);
        if ($res['data']) {
            $item = $res['data'];
            $params = ['id' => $item['id']];
            $dispatcher = app('Dingo\Api\Dispatcher');
            $dispatcher->post('internal/crawl/node_task/stop', $params);
        }
        // 更新任务状态为停止
        $params = ['id' => $taskId, 'status' => CrawlTask::IS_PAUSE];
        $dispatcher = app('Dingo\Api\Dispatcher');
        $res = $dispatcher->post('internal/crawl/task/status', $params);
        return $this->resObjectGet('任务停止成功', 'crawl_task.execute', $request->path());
    }
}
