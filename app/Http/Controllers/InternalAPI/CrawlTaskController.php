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
                return  $this->response->error($value, 401);
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
        $taskId = intval($request->get('id'));
        $status = intval($request->get('status'));
        if ($status == CrawlTask::IS_INIT) {
            // 生成脚本
            $params = ['task_id' => $taskId];
            $dispatcher = app('Dingo\Api\Dispatcher');
            $dispatcher->post('internal_api/crawl/task/generate_script', $params);
        }
        $params = ['task_id' => $taskId, 'status' => $status];
        $dispatcher = app('Dingo\Api\Dispatcher');
        $data = $dispatcher->post('internal_api/basic/crawl/task', $params);
        if ($data['status_code'] == 200) {
            return $data['data'];
        } else {
            return $data;
        }
    }

    /**
     * 生成抓取任务脚本文件
     * @param Request $request
     */
    public function generateScript(Request $request)
    {
        $taskId = intval($request->get('id'));
        //调用生成脚本接口
        $dispatcher = app('Dingo\Api\Dispatcher');
        $data = $dispatcher->get('internal_api/basic/crawl/task?id=' . $taskId);
        if ($data['status_code'] == 401) {
            return response('参数错误', 401);
        }
        $task = $data['data'];
        $now = time();
        $scriptFile = CrawlTask::SCRIPT_PATH . '/' . CrawlTask::SCRIPT_PREFIX . '_' . $task['id'] . '_' . $now . '.js';
        $params = ['id' => $taskId, 'update_script_last_generate_time' => $now];
        $dispatcher = app('Dingo\Api\Dispatcher');
        $data = $dispatcher->post('internal_api/basic/crawl/task/update_script_last_generate_time', $params);
        if ($data['status_code'] == 401) {
            return response('更新时间失败', 401);
        }
        if ($task['setting']['type'] == CrawlSetting::TYPE_GENERAL) {
            $content = $task['setting']['content'];
            $scriptContent = str_replace($content, '{{{URL}}}', $task['resource_url']);
        } else {
            //自定义模版类型
            $scriptContent = '';
        }
        generateScript($scriptFile, $scriptContent);
        return response('脚本生成成功', 200);
    }

    /**
     * 执行接口
     * @return mixed
     */
    public function execute(Request $request)
    {
        $taskId = intval($request->get('task_id'));
        $dispatcher = app('Dingo\Api\Dispatcher');
        $data = $dispatcher->get('internal_api/basic/crawl/task?id=' . $taskId);
        if ($data['status_code'] == 401) {
            return response('参数错误', 401);
        }
        $task = $data['data'];
        $scriptFile = CrawlTask::SCRIPT_PATH . '/' . CrawlTask::SCRIPT_PREFIX . '_' . $task->id . '_' . $task->script_last_generate_time . '.js';
        if (!file_exists($scriptFile)) {
            return response('脚本文件不存在', 401);
        }
        $command = 'casperjs ' . $scriptFile;
        exec($command, $output, $return);
        if ($return == 1) {
            return response($output, 401);
        }
        return response($output, 200);
    }
}
