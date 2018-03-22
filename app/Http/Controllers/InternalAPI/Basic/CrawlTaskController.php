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
        infoLog('[create] start.');
        $params = $request->all();
        infoLog('[create] validate.', $params);
        $validator = Validator::make($params, [
            'name' => 'string|nullable',
            'description' => 'string|nullable',
            'resource_url' => 'string|nullable',
            'cron_type' => 'integer|nullable',
            'selectors' => 'string|nullable',
            'response_type' => 'string|nullable',
            'response_url' => 'string|nullable',
            'keywords' => 'string|nullable',
            'response_param' => 'string|nullable',
            'setting_id' => 'integer|nullable',
            'protocol' => 'integer|nullable',
            'is_proxy' => 'integer|nullable',
            'is_ajax' => 'integer|nullable',
            'is_login' => 'integer|nullable',
            'is_wall' => 'integer|nullable'
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
        $fieldList = ['name', 'description', 'resource_url', 'cron_type', 'selectors', 'setting_id', 'protocol', 'is_proxy', 'is_ajax', 'is_login', 'is_wall', 'keywords'];
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
     * 获取任务详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function retrieve(Request $request)
    {
        infoLog('[retrieve] start.');
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

        $task = CrawlTask::with('setting')->find($params['id']);
        $data = [];
        if ($task) {
            $data = $task->toArray();
        }
        infoLog('[retrieve] end.', $data);
        return $this->resObjectGet($data, 'crawl_task', $request->path());
    }


    public function search(Request $request)
    {
        infoLog('[updateLastJobAt] start.');
        $params = $request->all();
        infoLog('[updateLastJobAt] validate start.');
        $validator = Validator::make($params, [
            'cron_type' => 'integer|required',
            'is_proxy' => 'integer|required',
            'protocol' => 'integer|required',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                infoLog('[updateLastJobAt] validate fail message.', $value);
                return $this->resError(401, $value);
            }
        }
        try {
            $items = CrawlTask::select('id,resource_url')
                                ->where('protocol', $params['protocol'])
                                ->where('is_proxy', $params['is_proxy'])
                                ->where('cron_type', $params['protocol'])
                                ->get();
            $data = [];
            if ($items) {
                $data = $items->toArray();
            }
        } catch (Exception $e) {
            errorLog($e->getMessage(), $e->getCode());
            return $this->resError($e->getCode(), $e->getMessage());
        }
        return $this->resObjectGet($data, 'crawl_task', $request->path());
    }
    public function update(Request $request)
    {
        infoLog('[update] start.');
        $params = $request->all();
        infoLog('[update] validate.', $params);
        $validator = Validator::make($params, [
            'name' => 'string|nullable',
            'description' => 'string|nullable',
            'resource_url' => 'nullable',
            'cron_type' => 'integer|nullable',
            'selectors' => 'nullable',
            'response_type' => 'string|nullable',
            'response_url' => 'nullable',
            'keywords' => 'nullable',
            'response_param' => 'string|nullable',
            'setting_id' => 'integer|nullable',
            'status' => 'integer|nullable',
            'protocol' => 'integer|nullable',
            'is_proxy' => 'integer|nullable',
            'is_ajax' => 'integer|nullable',
            'is_login' => 'integer|nullable',
            'is_wall' => 'integer|nullable',
            'start_time' => 'date|nullable',
            'last_job_at' => 'date|nullable',
            'test_time' => 'date|nullable',
            'test_result' => 'nullable',
            'id' => 'required|integer'
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            errorLog('[create] validate fail.', $errors);
            foreach ($errors->all() as $value) {
                errorLog('[create] validate fail message.', $value);
                return $this->resError(401, $value);
            }
        }
        $item = CrawlTask::find($params['id']);

        if (isset($params['name'])) {
            $item->name = $params['name'];
        }
        if (isset($params['description'])) {
            $item->description = $params['description'];
        }
        if (isset($params['resource_url'])) {
            $item->resource_url = $params['resource_url'];
        }
        if (isset($params['cron_type'])) {
            $item->cron_type = $params['cron_type'];
        }
        if (isset($params['selectors'])) {
            $item->selectors = $params['selectors'];
        }
        if (isset($params['response_type'])) {
            $item->response_type = $params['response_type'];
        }
        if (isset($params['response_url'])) {
            $item->response_url = $params['response_url'];
        }
        if (isset($params['keywords'])) {
            $item->keywords = $params['keywords'];
        }
        if (isset($params['response_param'])) {
            $item->response_param = $params['response_param'];
        }
        if (isset($params['setting_id'])) {
            $item->setting_id = $params['setting_id'];
        }
        if (isset($params['protocol'])) {
            $item->protocol = $params['protocol'];
        }
        if (isset($params['is_proxy'])) {
            $item->is_proxy = $params['is_proxy'];
        }
        if (isset($params['is_ajax'])) {
            $item->is_ajax = $params['is_ajax'];
        }
        if (isset($params['is_login'])) {
            $item->is_login = $params['is_login'];
        }
        if (isset($params['is_wall'])) {
            $item->is_wall = $params['is_wall'];
        }

        if (isset($params['status'])) {
            $item->status = $params['status'];
        }
        if (isset($params['start_time'])) {
            $item->start_time = $params['start_time'];
        }
        if (isset($params['last_job_at'])) {
            $item->last_job_at = $params['last_job_at'];
        }
        if (isset($params['test_time'])) {
            $item->test_time = $params['test_time'];
        }
        if (isset($params['test_result'])) {
            $item->test_result = $params['test_result'];
        }

        try {
            if ($item->save()) {
                $result = $item->toArray();
            }
        } catch (Exception $e) {
            errorLog($e->getMessage(), $e->getCode());
            return $this->resError($e->getCode(), $e->getMessage());
        }
        return $this->resObjectGet($result, 'crawl_task', $request->path());
    }
}
