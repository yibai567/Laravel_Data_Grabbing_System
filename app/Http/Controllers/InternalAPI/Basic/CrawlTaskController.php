<?php

namespace App\Http\Controllers\InternalAPI\Basic;

use App\Models\CrawlTask;
use App\Services\ValidatorService;
use Illuminate\Http\Request;
use App\Http\Controllers\InternalAPI\Controller;

/**
 * CrawlTaskController
 * 抓取任务基础API控制器
 *
 * @author: yuwenbin@jinse.com
 * @version: v1.0
 */
class CrawlTaskController extends Controller
{
    /**
     * create
     * 创建爬虫任务接口-基础接口
     *
     * @param Request $request
     * @return json
     */
    public function create(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
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
            'is_wall' => 'integer|nullable',
            'resource_type' => 'integer|nullable',
            'header' => 'nullable',
            'md5_params' => 'string|required',
            'api_fields' => 'string|nullable',
        ]);
        $params['selectors'] = json_encode($params['selectors']);
        $params['api_fields'] = json_encode($params['api_fields']);
        $params['status'] = CrawlTask::IS_INIT;
        $task = CrawlTask::create($params);

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
        ValidatorService::check($params, [
            "id" => "integer|required",
        ]);

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

        ValidatorService::check($params, [
            // 'cron_type' => 'integer|nullable',
            // 'is_proxy' => 'integer|nullable',
            // 'protocol' => 'integer|nullable',
            //'resource_url' => 'string|nullable',
            'md5_params' => 'string|nullable',
        ]);

        try {
            $items = CrawlTask::select('id', 'resource_url')
                                // ->where('protocol', $params['protocol'])
                                // ->where('is_proxy', $params['is_proxy'])
                                // ->where('cron_type', $params['protocol'])
                                ->where('md5_params', $params['md5_params'])
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

        ValidatorService::check($params, [
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
            'resource_type' => 'integer|nullable',
            'header' => 'nullable',
            'md5_params' => 'string|nullable',
            'api_fields' => 'string|nullable',
            'id' => 'required|integer'
        ]);
        if (!empty($params['selectors'])) {
            $params['selectors'] = json_encode($params['selectors'], true);
        }
        if (!empty($params['api_fields'])) {
            $params['api_fields'] = json_encode($params['api_fields'], true);
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

        ValidatorService::check($params, [
            'ids' => 'string|required|max:100',
        ]);

        $ids = explode(',', $params['ids']);
        $tasks = CrawlTask::whereIn('id', $ids)->get();

        $data = [];
        if (!empty($tasks)) {
            $data = $tasks->toArray();
        }

        return $this->resObjectGet($data, 'list', $request->path());
    }
}
