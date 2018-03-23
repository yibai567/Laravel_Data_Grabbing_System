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
                return $this->resError(401, $value);
            }
        }

        if (empty($params['result'])) {
            infoLog('[basic:createForBatch] result empty!');
            return $this->resObjectGet($params['task_id'], 'crawl_result', $request->path());
        }

        $items = [];

        foreach ($params['result'] as $value) {
            $item['format_data'] = json_encode($value);
            $item['original_data'] = md5($item['format_data']);
            $item['status'] = CrawlResult::IS_UNTREATED;
            $item['crawl_task_id'] = $params['task_id'];
            $item['task_start_time'] = $params['start_time'];
            $item['task_end_time'] = $params['end_time'];
            $item['task_url'] = $value['url'];
            $items[] = $item;
        }

        $result = CrawlResult::insert($items);

        if (!$result) {
            errorLog('[basic:createByBatch] insert fail.', $items);
            return $this->resError(402, '插入失败');
        }

        infoLog('[basic:createByBatch] end.');
        return $this->resObjectGet($params, 'list', $request->path());
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
            "original_data" => "nullable|string",
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                return  $this->response->error($value, 401);
            }
        }

        try {
            $items = CrawlResult::where(function ($query) use ($params) {

                if (!empty($params['task_id'])) {
                    $query->where('crawl_task_id', $params['task_id']);
                }

                if (!empty($params['original_data'])) {
                    $query->where('original_data', $params['original_data']);
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
