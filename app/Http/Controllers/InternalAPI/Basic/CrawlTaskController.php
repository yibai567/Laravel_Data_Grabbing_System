<?php

namespace App\Http\Controllers\InternalAPI\Basic;

use App\Models\CrawlTask;
use Dompdf\Exception;
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
        $fieldList = ['name', 'description', 'resource_url', 'cron_type', 'selectors'];
        $data = array_only($params, $fieldList);
        $data['status'] = CrawlTask::IS_INIT;
        $data['response_type'] = CrawlTask::RESPONSE_TYPE_API;
        infoLog('[create] prepare data.', $data);
        $task = CrawlTask::create($data);
        infoLog('[create] create task.', $task);
        infoLog('[create] end.', $task);
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
        infoLog('[updateStatus] start.', $request);
        $params = $request->all();
        infoLog('[updateStatus] validate.', $params);
        $validator = Validator::make($params, [
            "id" => "integer|required",
            "status" => "integer|required"
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
        try {
            $taskId = $params['id'];
            $status = $params['status'];
            $statusArr = [
                CrawlTask::IS_INIT,
                CrawlTask::IS_TEST_SUCCESS,
                CrawlTask::IS_TEST_ERROR,
                CrawlTask::IS_START_UP,
                CrawlTask::IS_PAUSE,
                CrawlTask::IS_ARCHIEVE,
            ];
            infoLog('[updateStatus] check the params.', $status);
            if (!in_array($status, $statusArr)) {
                throw new Exception('[updateStatus] status param error', 401);
            }
            infoLog('[updateStatus] get the task.', $taskId);
            $task = CrawlTask::find($taskId);
            infoLog('[updateStatus] get the task.', $task);
            $data = [];
            if (empty($task)) {
                throw new Exception('[updateStatus] task not exist.', 401);
            }
            $task->status = $status;
            if (!$task->save()) {
                infoLog('[updateStatus] save fail.', $task);
                throw new Exception('save failed.', 401);
            }
            $data = $task->toArray();
        } catch (Exception $e) {
            errorLog($e->getMessage(), $e->getCode());
            return $this->resError($e->getCode(), $e->getMessage());
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
        infoLog('[retrieve] start.', $request);
        $params = $request->all();
        infoLog('[retrieve] validate.', $params);
        $validator = Validator::make($params, [
            "id" => "integer|required",
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            errorLog('[retrieve] validate fail.', $errors);
            foreach ($errors->all() as $value) {
                errorLog('[retrieve] validate fail message.', $value);
                return $this->resError(401, $value);
            }
        }
        infoLog('[retrieve] validate end.', $params);
        $task = CrawlTask::with('setting')->find($params['id']);
        infoLog('[retrieve] get data of task.', $task);
        $data = [];
        if ($task) {
            $data = $task->toArray();
        }
        infoLog('[retrieve] end.', $data);
        return $this->resObjectGet($data, 'crawl_task', $request->path());
    }

    /**
     * 更新脚本文件
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateScriptFile(Request $request)
    {
        infoLog('[updateScriptFile] start.', $request);
        $params = $request->all();
        infoLog('[updateScriptFile] validate.', $params);
        $validator = Validator::make($params, [
            "id" => "integer|required",
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            errorLog('[updateScriptFile] validate fail.', $errors);
            foreach ($errors->all() as $value) {
                errorLog('[updateScriptFile] validate fail message.', $value);
                return $this->resError(401, $value);
            }
        }
        infoLog('[updateScriptFile] validate end.', $params);
        try {
            $taskId = $params['id'];
            $scriptFile = CrawlTask::SCRIPT_PREFIX . '_' . $taskId . '_' . date('YmdHis') . '.js';
            infoLog('[updateScriptFile] get script file name', [$taskId, $scriptFile]);
            $task = CrawlTask::find($taskId);
            infoLog('[updateScriptFile] get task', $task);
            if (empty($task)) {
                throw new Exception('task not exist', 401);
            }
            if ($task->script_file) {
                $task->last_script_file = $task->script_file;
            }
            $task->script_file = $scriptFile;
            if (!$task->save()) {
                errorLog('[updateScriptFile] save fail', $task);
                throw new Exception('[updateScriptFile] task save fail', 401);
            }
            infoLog('[updateScriptFile] save success', $task);
        } catch (Exception $e) {
            errorLog($e->getMessage());
            return $this->resError($e->getCode(), $e->getMessage());
        }

        infoLog('[updateScriptFile] end', $task);
        return $this->resObjectGet($task, 'crawl_task', $request->path());
    }
}
