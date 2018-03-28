<?php

namespace App\Http\Controllers\InternalAPI\Basic;

use App\Services\ValidatorService;
use App\Models\CrawlResult;
use App\Models\CrawlResultV2;
use App\Http\Controllers\InternalAPI\Controller;
use Illuminate\Http\Request;

/**
 * CrawlResultController
 * 抓取任务基础结果API控制器
 *
 * @author: yuwenbin@jinse.com
 * @version: v1.0
 */
class CrawlResultController extends Controller
{
    /**
     * createForBatch
     * 支持单条，批量插入数据
     *
     * @param Request $request
     * @return json
     */
    public function createForBatch(Request $request)
    {
        $params = $request->all();
        ValidatorService::check($params, [
            'task_id' => 'required|integer',
            'is_test' => 'integer|nullable',
            'start_time' => 'date|nullable',
            'end_time' => 'date|nullable',
            'result' => 'nullable',
        ]);

        if (empty($params['result'])) {
            infoLog('[basic:createForBatch] result empty!');
            return $this->resError(401, 'result is empty!');
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
        ValidatorService::check($request->all(), [
            "limit" => "nullable|integer|min:1|max:500",
            "offset" => "nullable|integer|min:0",
        ]);

        if (empty($params['limit'])) {
            $params['limit'] = 20;
        }
        if (empty($params['offset'])) {
            $params['offset'] = 0;
        }

        try {
            $items = CrawlResult::take($params['limit'])
                                ->skip($params['offset'])
                                ->orderBy('id', 'desc')
                                ->get();

            $result = [];
            if (!empty($items)) {
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
        ValidatorService::check($request->all(), [
            "task_id" => "nullable|integer",
            "url" => "nullable|string",
            "original_data" => "nullable|string",
        ]);

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

            if (!empty($items)) {
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
    public function searchV2(Request $request)
    {
        $params =$request->all();
        ValidatorService::check($request->all(), [
            "task_id" => "nullable|integer",
            "url" => "nullable|string",
            "md5_fields" => "nullable|string",
        ]);

        try {
            $items = CrawlResultV2::where(function ($query) use ($params) {

                if (!empty($params['task_id'])) {
                    $query->where('crawl_task_id', $params['task_id']);
                }

                if (!empty($params['md5_fields'])) {
                    $query->where('md5_fields', $params['md5_fields']);
                }

                if (!empty($params['url'])) {
                    $query->where('task_url', $params['url']);
                }
            })->get();

            //$query->take($params['limit']);
            //$query->skip($params['offset']);
            $result = [];

            if (!empty($items)) {
                $result = $items->toArray();
            }
        } catch (Exception $e) {
            errorLog($e->getMessage(), $e->getCode());
            return $this->resError($e->getCode(), $e->getMessage());
        }

        return $this->resObjectGet($result, 'list', $request->path());
    }
}
