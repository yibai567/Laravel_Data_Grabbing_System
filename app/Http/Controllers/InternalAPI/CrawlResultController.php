<?php

namespace App\Http\Controllers\InternalAPI;

use App\Http\Requests\CrawlResultCreateRequest;
use App\Http\Controllers\InternalAPI\Controller;
use Illuminate\Http\Request;
use App\Models\CrawlResult;
use Illuminate\Support\Facades\Validator;
use App\Services\APIService;


class CrawlResultController extends Controller
{
    /**
     * 支持单条，批量插入数据
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
    */
    public function createByBatch(Request $request)
    {
        infoLog('[createByBatch] start.');
        $params = $request->all();
        $validator = Validator::make($params, [
            'data' => 'nullable',
        ]);
        infoLog('[createByBatch] params validator start.');
        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                errorLog('[createByBatch] params validator fail.', $value);
                return response($value, 401);
            }
        }
        infoLog('[createByBatch123] params validator end.');
        $result = [];
        if (empty($params['data'])) {
            infoLog('[createByBatch] data empty!');
            return $this->resObjectGet($result, 'crawl_result', $request->path());
        }
        $formatData = [];
        foreach ($params['data'] as $key => $value) {
            foreach ($value['data'] as $valueKey => $data) {
                if (!empty($value['setting_keywords'])) {
                    $matchingResult  = strpos($data['text'], $value['setting_keywords']);
                    if ($matchingResult !== false){
                        $newData[] = $data;
                    }
                } else {
                    $newData[] = $data;
                }
            }
            $is_test = $value['is_test'];
            $value['format_data'] = $newData;
            if (!empty($value['format_data'])) {
                foreach ($value['format_data'] as $formatKey => $formatValue) {
                    $newParams['task_url'] = $formatValue['url'];
                    $newParams['format_data'] = $formatValue['text'];
                    //$newParams['original_data'] = $value;
                    $newParams['status'] = CrawlResult::IS_UNTREATED;
                    $crawl_task_id = $value['crawl_task_id'];
                    $newParams['crawl_task_id'] = $value['crawl_task_id'];
                    $newParams['task_start_time'] = $value['task_start_time'];
                    $newParams['task_end_time'] = $value['task_end_time'];
                    $newParams['setting_selectors'] = $value['setting_selectors'];
                    $newParams['setting_keywords'] = $value['setting_keywords'];
                    $newParams['setting_data_type'] = $value['setting_data_type'];
                    $formatData['data'][] = $newParams;
                }
            }
        }
        if (empty($formatData)) {
            return $this->resObjectGet($formatData, 'formatData empty.', $request->path());
        }


        if ($is_test == CrawlResult::EFFECT_TEST) {
            $resultArr = [];
            $resultArr['is_test'] = $is_test;
            foreach ($formatData['data'] as $key => $value) {
                $newArr['title'] = $value['format_data'];
                $newArr['url'] = $value['task_url'];
                $newArr['task_id'] = $value['crawl_task_id'];
                $resultArr['result'][] = $newArr;
            }
            //请求第三方接口
            return $this->resObjectGet($formatData, 'crawl_result', $request->path());
        }

        $dispatcher = app('Dingo\Api\Dispatcher');
        infoLog('[createByBatch] request internal/basic createByBatch start');
        $resultData = APIService::basePost('/internal/basic/crawl/result/batch_result', $formatData, 'json');
        if ($resultData['status_code'] != 200) {
            errorLog('[createByBatch] request internal/basic createByBatch result error', $resultData);
            return response($resultData['message'], $resultData['status_code']);
        }
        if ($resultData['data']) {
            $result = $resultData['data'];
        }
                infoLog('测试修改', $result);exit;

        infoLog('[createByBatch] request internal/basic createByBatch end');
        infoLog('[createByBatch] end.');

        $taskParams['id'] = $crawl_task_id;
        $taskParams['last_job_at'] = date('Y-m-d H:i:s');
        $taskResult = APIService::internalPost('/internal/crawl/task/last_job_at', $taskParams, 'json');
        if ($taskResult['status_code'] != 200) {
            errorLog('[createByBatch] request /v1/crawl/task/last_job_at createByBatch result error', $taskParams);
            return response($taskResult['message'], $taskResult['status_code']);
        }
        //调用第三方接口
        // foreach ($result as $key => $value) {

        // }
        return $this->resObjectGet($result, 'crawl_result', $request->path());
    }
 }
