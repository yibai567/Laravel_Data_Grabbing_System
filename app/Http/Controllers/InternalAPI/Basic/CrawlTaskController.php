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
     * create
     * 创建爬虫任务接口-基础接口
     *
     * @param CrawlTaskCreateRequest $request
     * @return json
     */
    public function create(Request $request)
    {
        infoLog('[create] start.');
        $params = $request->all();

        $validator = Validator::make($params, [
            'name' => 'string|nullable',
            'description' => 'string|nullable',
            'resource_url' => 'string|nullable',
            'cron_type' => 'integer|nullable',
            'selectors' => 'nullable',
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

            foreach ($errors->all() as $value) {
                errorLog('[create] validate fail message.', $value);
                return $this->resError(401, $value);
            }
        }

        $params['selectors'] = json_encode($params['selectors']);

        $fieldList = ['name', 'description', 'resource_url', 'cron_type', 'selectors', 'setting_id', 'protocol', 'is_proxy', 'is_ajax', 'is_login', 'is_wall', 'keywords'];

        $data = array_only($params, $fieldList);
        $data['status'] = CrawlTask::IS_INIT;
        $data['response_type'] = CrawlTask::RESPONSE_TYPE_API;
        $task = CrawlTask::create($data);

        return $this->resObjectGet($task, 'crawl_task', $request->path());
    }

    /**
     * retrieve
     * 获取任务详情
     *
     * @param Request $request
     * @return json
     */
    public function retrieve(Request $request)
    {
        $params = $request->all();
        $validator = Validator::make($params, [
            "id" => "integer|required",
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();

            foreach ($errors->all() as $value) {
                errorLog('[retrieve] validate fail message.', $value);
                return $this->resError(401, $value);
            }
        }

        $task = CrawlTask::with('setting')->find($params['id']);
        $data = [];

        if (!empty($task)) {
            $data = $task->toArray();
        }

        return $this->resObjectGet($data, 'crawl_task', $request->path());
    }


    /**
     * search
     * 任务查询接口-根据条件查询
     *
     * @param Request $request
     * @return json
     */
    public function search(Request $request)
    {
        $params = $request->all();

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
            if (!empty($items)) {
                $data = $items->toArray();
            }
        } catch (Exception $e) {
            errorLog($e->getMessage(), $e->getCode());
            return $this->resError($e->getCode(), $e->getMessage());
        }

        return $this->resObjectGet($data, 'crawl_task', $request->path());
    }

    /**
     * update
     * 更新任务信息
     *
     * @param Request $request
     * @return json
     */
    public function update(Request $request)
    {
        $params = $request->all();

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

            foreach ($errors->all() as $value) {
                errorLog('[create] validate fail message.', $value);
                return $this->resError(401, $value);
            }
        }

        $item = CrawlTask::find($params['id']);

        foreach ($params as $key=>$value) {
            if (isset($value)) {
                $item->{$key} = $value;
            }
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

    /**
     * listByIds
     * 根据id列表获取任务列表
     *
     * @param Request $request
     * @return json
     */
    public function listByIds(Request $request)
    {
        $params = $request->all();

        $validator = Validator::make($params, [
            'ids' => 'string|required|max:100',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();

            foreach ($errors->all() as $value) {
                infoLog('[listByIds] validate fail message.', $value);
                return $this->resError(401, $value);
            }
        }

        $ids = explode(',', $params['ids']);
        $tasks = CrawlTask::whereIn('id', $ids)->get();

        $data = [];
        if (!empty($tasks)) {
            $data = $tasks->toArray();
        }

        return $this->resObjectGet($data, 'list', $request->path());
    }
}
