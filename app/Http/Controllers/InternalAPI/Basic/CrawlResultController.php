<?php

namespace App\Http\Controllers\InternalAPI\Basic;

use DB;
use App\Http\Requests\CrawlResultCreateRequest;
use App\Models\CrawlResult;
use App\Http\Controllers\InternalAPI\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CrawlResultController extends Controller
{
    /**
     * 支持单条，批量插入数据
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createForBatch(Request $request)
    {
        $params = $request->all();
        infoLog("[basic:createForBatch] start.");
        $validator = Validator::make($params, [
            'task_id' => 'required|integer',
            'is_test' => 'integer|nullable',
            'start_time' => 'date|nullable',
            'end_time' => 'date|nullable',
            'result' => 'nullable',
        ]);
        infoLog('[basic:createForBatch] params validator start');
        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                errorLog('[basic:createForBatch] params validator fail', $value);
                return response($value, 401);
            }
        }
        infoLog('[basic:createForBatch] params validator end.');

        if (empty($params['result'])) {
            infoLog('[basic:createForBatch] result empty!');
            return $this->resObjectGet($params['task_id'], 'crawl_result', $request->path());
        }
        $items = [];
        foreach ($params['data'] as $item) {
            $item['format_data'] = json_encode($item['format_data']);
            $item['status'] = CrawlResult::IS_PROCESSED;
            unset($item['data'],$item['is_test']);
            $items[] = $item;
        }
        if (empty($items)) {
            infoLog('[createByBatch] items empty!');
            return $this->resObjectGet($result, 'crawl_result', $request->path());
        }
        $result = CrawlResult::insert($items);
        if (!$result) {
            errorLog('[createByBatch] insert fail.', $items);
            return response('插入失败', 402);
        }
        infoLog('[createByBatch] insert end.', $result);
        infoLog('[createByBatch] end.');
        return $this->resObjectGet($items, 'crawl_result', $request->path());
    }

    /**
     * 任务结果列表
     * @param $taskId
     * @param $taskUrl
     * @return bool
     */
    public function isTaskExist(Request $request)
    {
        $params = $request->all();
        $validator = Validator::make($params, [
            'id' => 'integer|required',
            'url' => 'string|nullable',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                return $this->resError(401, $value);
            }
        }
        $result = CrawlResult::where(['task_url' => $params['url'], 'crawl_task_id' => $params['id']])
            ->first();
        $data = [];
        if ($result) {
            $data = $result->toArray();
        }
        return $this->resObjectGet($data, 'crawl_result', $request->path());
    }
    /**
     * search
     * 根据条件获取结果纪录
     *
     * @param Request $request
     * @return array
     */
    public function all(Request $request)
    {
        $params =$request->all();
        //数据验证规则
        $validator = Validator::make($request->all(), [
            "limit" => "nullable|integer|min:1|max:500",
            "offset" => "nullable|integer|min:0",
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                return  $this->response->error($value, 401);
            }
        }
        try {
            $items = CrawlResult::take($params['limit'])
                                ->skip($params['offset'])
                                ->orderBy('id', 'desc')
                                ->get();
            $result = [];
            if ($items) {
                $result = $items->toArray();
            }
        } catch (Exception $e) {
            errorLog($e->getMessage(), $e->getCode());
            return $this->resError($e->getCode(), $e->getMessage());
        }
        return $this->resObjectGet($result, 'list', $request->path());
    }
    /**
     * search
     * 根据复杂条件获取结果纪录
     *
     * @param Request $request
     * @return array
     */
    public function search(Request $request)
    {
        $params =$request->all();
        //数据验证规则
        $validator = Validator::make($request->all(), [
            "task_id" => "nullable|integer",
            "url" => "nullable|string",
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                return  $this->response->error($value, 401);
            }
        }

        try {
            $items = CrawlResult::where(function ($query) use ($params) {
                if (!empty($params['id'])) {
                    $query->where('crawl_task_id', $params['task_id']);
                }
                if (!empty($params['url'])) {
                    $query->where('task_url', $params['url']);
                }
            })->get();

            //$query->take($params['limit']);
            //$query->skip($params['offset']);
            $result = [];
            if ($items) {
                $result = $items->toArray();
            }
        } catch (Exception $e) {
            errorLog($e->getMessage(), $e->getCode());
            return $this->resError($e->getCode(), $e->getMessage());
        }
        return $this->resObjectGet($result, 'list', $request->path());
    }
}
