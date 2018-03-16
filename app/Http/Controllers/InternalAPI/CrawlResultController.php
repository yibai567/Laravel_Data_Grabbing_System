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
        infoLog('[createByBatch] params validator end.');
        if (empty($params['data'])) {
            infoLog('[createByBatch] data empty!');
            return $this->resObjectGet([], 'crawl_result', $request->path());
        }
        $formatData = [];
        $is_test = $params['data'][0]['is_test'];
        $crawl_task_id = $params['data'][0]['crawl_task_id'];
        foreach ($params['data'] as $crawlResult) {
            if (empty($crawlResult['data'])) {
                return $this->resObjectGet([], 'crawl_result', $request->path());
            }
            foreach ($crawlResult['data'] as $crawlResultValue) {
                $crawlResult['task_url'] = $crawlResultValue['url'];
                $crawlResult['format_data'] = $crawlResultValue['text'];
                unset($crawlResult['data'], $crawlResult['is_test']);
                if (!empty($crawlResult['setting_keywords'])) {
                    $setting_keywords = explode(',', $crawlResult['setting_keywords']);
                    foreach ($setting_keywords as $keywordsValue) {
                        $matchingResult  = strpos($crawlResultValue['text'], $keywordsValue);
                        if ($matchingResult !== false){
                            $formatData['data'][] = $crawlResult;
                        }
                    }
                } else {
                    $formatData['data'][] = $crawlResult;
                }
            }
        }
        if (empty($formatData['data'])) {
            return $this->resObjectGet($formatData, 'formatData empty.', $request->path());
        }

        if (empty($is_test)) {
            $is_test = 2;
        }

        if ($is_test == CrawlResult::EFFECT_TEST) {
            $platformData = [];
            $platformData['is_test'] = $is_test;
            foreach ($formatData['data'] as $platformValue) {
                $newParams['title'] = $platformValue['format_data'];
                $newParams['url'] = $platformValue['task_url'];
                $newParams['task_id'] = $platformValue['crawl_task_id'];
                $platformData['result'][] = $newParams;
            }

            //$platformResult = APIService::httpPost('http://boss.lsjpic.com/api/live/put_craw_data', $platformData, 'json');
            //dd($platformResult);
            //print_r($platformResult);exit;
            //请求第三方接口
            return $this->resObjectGet($formatData, 'crawl_result', $request->path());
        }
        //去重复
        $newFormatData['data'] = [];
        foreach ($formatData['data'] as $repeatedValue) {
            $repeatedDada = APIService::baseGet('/internal/basic/crawl/result/is_task_exist', ['id' => $repeatedValue['crawl_task_id'], 'url' => $repeatedValue['task_url']]);
            if ($repeatedDada['status_code'] != 200) {
                errorLog('[createByBatch] request /internal/basic/crawl/result/is_task_exist result error', ['id' => $crawlResult['crawl_task_id']]);
                return response($repeatedDada['message'], $repeatedDada['status_code']);
            }
            if (empty($repeatedDada['data'])) {
                $newFormatData['data'][] = $repeatedValue;
            }
        }
        if (empty($newFormatData['data'])) {
            infoLog('[createByBatch] repeated data');
            return $this->resObjectGet([], 'crawl_result', $request->path());
        }
        infoLog('[createByBatch] request internal/basic createByBatch start');
        $resultData = APIService::basePost('/internal/basic/crawl/result/batch_result', $newFormatData, 'json');
        if ($resultData['status_code'] != 200) {
            errorLog('[createByBatch] request internal/basic createByBatch result error', $newFormatData);
            return response($resultData['message'], $resultData['status_code']);
        }
        $result = [];
        if ($resultData['data']) {
            $result = $resultData['data'];
            // //调用第三方接口
            // foreach ($result as $key => $value) {
            //     $
            //     print_r($value);
            // }
        }
        infoLog('[createByBatch] request internal/basic createByBatch end');
        infoLog('[createByBatch] end.');



        $taskParams['id'] = $crawl_task_id;
        $taskParams['last_job_at'] = date('Y-m-d H:i:s');
        $taskResult = APIService::internalPost('/internal/crawl/task/last_job_at', $taskParams, 'json');
        if ($taskResult['status_code'] != 200) {
            errorLog('[createByBatch] request /v1/crawl/task/last_job_at createByBatch result error', $taskParams);
            return response($taskResult['message'], $taskResult['status_code']);
        }

        return $this->resObjectGet($result, 'crawl_result', $request->path());
    }
 }
