<?php

namespace App\Http\Controllers\InternalAPI;

use App\Http\Requests\CrawlResultCreateRequest;
use App\Http\Controllers\InternalAPI\Controller;
use Illuminate\Http\Request;
use App\Models\CrawlResult;
use Illuminate\Support\Facades\Validator;


class CrawlResultController extends Controller
{
    /**
     * 支持单条，批量插入数据
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
    */
    public function createByBatch(Request $request)
    {
        $params = $request->all();
        infoLog('[createByBatch] start.', $params);
        $validator = Validator::make($params, [
            'data' => 'nullable',
            'crawl_task_id' => 'numeric|nullable',
            'task_start_time' => 'date|nullable',
            'task_end_time' => 'date|nullable',
            'task_url' => 'string|nullable',
            'setting_selectors' => 'string|nullable',
            'setting_keywords' => 'string|nullable',
            'setting_data_type' => 'numeric|nullable',
            'effect' => 'numeric|nullable',
        ]);
        infoLog('[createByBatch] params validator start.');
        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                errorLog('[createByBatch] params validator fail.', $value);
                return response($value, 401);
            }
        }
        infoLog('[createByBatch] params validator end.');
        $result = [];
        if (empty($params['data'])) {
            infoLog('[createByBatch] data empty!');
            return $this->resObjectGet($result, 'crawl_result', $request->path());
        }

        $params['original_data'] = $params['data'];
        $newData = [];
        infoLog('[createByBatch] setting_keywords matching start',  $params['setting_keywords']);
        foreach ($params['data'] as $key => $value) {
            $matchingResult  = strpos($value['text'], $params['setting_keywords']);
            if ($matchingResult !== false){
                $newData[] = $value;
            }
        }
        infoLog('[createByBatch] setting_keywords matching end', $newData);
        if (empty($newData)) {
            infoLog('[createByBatch] newData empty!');
            return $this->resObjectGet($result, 'crawl_result', $request->path());
        }
        $params['format_data'] = $newData;

        if ($params['effect'] == CrawlResult::EFFECT_TEST) {
            infoLog('[createByBatch] effect is test', $newData);
            return $this->resObjectGet($newData, 'crawl_result', $request->path());
        }

        unset($params['data'], $params['effect']);
        $dispatcher = app('Dingo\Api\Dispatcher');

        infoLog('[createByBatch] request internal/basic createByBatch start', $params);
        $resultData = $dispatcher->json($params)->post(config('url.jinse_internal_url') . '/internal/basic/crawl/result/batch_result');
        if ($resultData['status_code'] != 200) {
            errorLog('[createByBatch] request internal/basic createByBatch result error', $resultData);
            return response($resultData['message'], $resultData['status_code']);
        }
        if ($resultData['data']) {
            $result = $resultData['data'];
        }
        infoLog('[createByBatch] request internal/basic createByBatch end');
        infoLog('[createByBatch] end.', $result);
        return $this->resObjectGet($result, 'crawl_result', $request->path());
    }
 }
