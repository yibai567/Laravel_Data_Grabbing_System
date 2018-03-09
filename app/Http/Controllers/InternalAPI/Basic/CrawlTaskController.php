<?php

namespace App\Http\Controllers\InternalAPI\Basic;

use App\Models\CrawlTask;
use Illuminate\Http\Request;
use App\Http\Controllers\InternalAPI\Controller;
use Illuminate\Support\Facades\Validator;

class CrawlTaskController extends Controller
{
    /**
     * 创建爬虫任务接口-基础接口
     * @param CrawlTaskCreateRequest $request
     * @return json
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
        $data = [
            'name' => $request->name,
            'description' => $request->description,
            'resource_url' => $request->resource_url,
            'cron_type' => intval($request->cron_type),
            'selectors' => $request->selectors,
            'status' => CrawlTask::IS_INIT,
            'response_type' => CrawlTask::RESPONSE_TYPE_API,
            'response_url' => '',
            'response_params' => '',
            'test_time' => null,
            'test_result' => '',
        ];
        $task = CrawlTask::create($data);
        return $this->resObjectGet($task, 'crawl_task', $request->path());
    }

    /**
     * 更新任务状态接口
     * @param $taskId
     * @param int $status
     * @return json
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
        $statusArr = [
            CrawlTask::IS_INIT,
            CrawlTask::IS_TEST_SUCCESS,
            CrawlTask::IS_TEST_ERROR,
            CrawlTask::IS_START_UP,
            CrawlTask::IS_PAUSE,
            CrawlTask::IS_ARCHIEVE,
        ];
        if (!in_array($status, $statusArr)) {
            return response('status 参数错误', 401);
        }

        $task = CrawlTask::find($taskId);
        $data = [];
        if (empty($task)) {
            return  $this->response->error('任务不存在', 401);
        }
        $data = $task->toArray();
        if ($task->status !== $status) {
            $task->status = $status;
            $task->save();
        }

        return $this->resObjectGet($data, 'crawl_task', $request->path());
    }

    /**
     * 获取任务详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function retrieve(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id" => "integer|required",
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                return  response($value, 401);
            }
        }

        $taskId = intval($request->get('id'));
        $task = CrawlTask::with('setting')->find($taskId);
        $data = [];
        if ($task) {
            $data = $task->toArray();
        }
        return $this->resObjectGet($data, 'crawl_task', $request->path());
    }

    /**
     * 更新脚本最后更新时间
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateScriptLastGenerateTime(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id" => "integer|required",
            "script_last_generate_time" => "integer|required"
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                return  $this->response->error($value, 401);
            }
        }

        $taskId = intval($request->get('id'));
        $scriptLastGenerateTime = intval($request->get('script_last_generate_time'));
        if (empty($scriptLastGenerateTime)) {
            $scriptLastGenerateTime = time();
        }
        $scriptFile = CrawlTask::SCRIPT_PATH . '/' . CrawlTask::SCRIPT_PREFIX . '_' . $taskId . '_' . $scriptLastGenerateTime . '.js';
        $task = CrawlTask::findOrFail($taskId);
        $task->script_last_generate_time = $scriptLastGenerateTime;
        $task->script_file = $scriptFile;
        $task->save();
        $data = $task->toArray();
        return $this->resObjectGet($data, 'crawl_task', $request->path());
    }

    /**
     * 更新脚本文件
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateScriptFile(Request $request)
    {
        infoLog('抓取平台基础接口更新脚本文件接口启动');
        $validator = Validator::make($request->all(), [
            "id" => "integer|required",
        ]);
        infoLog('抓取平台基础接口更新脚本文件接口参数验证', $validator);
        if ($validator->fails()) {
            infoLog('抓取平台基础接口更新脚本文件接口参数验证错误', $validator->fails());
            $errors = $validator->errors();
            infoLog('抓取平台基础接口更新脚本文件接口参数验证错误', $errors);
            foreach ($errors->all() as $value) {
                infoLog('抓取平台基础接口更新脚本文件接口参数验证错误详情', $value);
                return  $this->response->error($value, 401);
            }
            infoLog('抓取平台基础接口更新脚本文件接口参数验证错误完成');
        }
        infoLog('抓取平台基础接口更新脚本文件接口参数验证完成');
        $taskId = intval($request->get('id'));
        $scriptFile = CrawlTask::SCRIPT_PATH . '/' . CrawlTask::SCRIPT_PREFIX . '_' . $taskId . '_' . date('Y-m-d-His') . '.js';
        infoLog('抓取平台基础接口更新脚本文件接口参数准备', [$taskId, $scriptFile]);
        $task = CrawlTask::find($taskId);
        if (empty($task)) {
            response('任务不存在', 401);
        }
        if ($task->script_file) {
            $task->last_script_file = $task->script_file;
        }
        $task->script_file = $scriptFile;
        $task->save();
        infoLog('抓取平台基础接口更新脚本文件接口完成');
        return $this->resObjectGet($task, 'crawl_task', $request->path());
    }
}
