<?php

namespace App\Http\Controllers\InternalAPI;

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
    public function createForBatch(Request $request)
    {
        infoLog('[internal:createForBatch] start.');
        $params = $request->all();
        $validator = Validator::make($params, [
            'task_id' => 'required|integer',
            'is_test' => 'integer|nullable',
            'start_time' => 'date|nullable',
            'end_time' => 'date|nullable',
            'result' => 'nullable',
        ]);
        infoLog('[internal:createForBatch] params validator start.');
        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                errorLog('[internal:createForBatch] params validator fail.', $value);
                return resError(401, $value);
            }
        }
        infoLog('[internal:createForBatch] params validator end.');

        //更新任务表last_job_at字段
        infoLog('[internal:createForBatch] update t_crawl_task last_job_at start.');
        // $taskParams = [];
        // $taskParams['id'] = $params['task_id'];
        // $taskParams['last_job_at'] = date('Y-m-d H:i:s');
        // $taskResult = APIService::internalPost('/internal/basic/crawl/task/update', $taskParams, 'json');
        // if ($taskResult['status_code'] != 200) {
        //     errorLog('[internal:createForBatch] update t_crawl_task last_job_at error', $taskParams);
        //     return resError($taskResult['status_code'], $taskResult['message']);
        // }
        infoLog('[internal:createForBatch] update t_crawl_task last_job_at end.');

        if (empty($params['result'])) {
            infoLog('[internal:createForBatch] result empty!');
            return $this->resObjectGet($params, 'crawl_result', $request->path());
        }


        if (empty($is_test)) {
            $is_test = 0;
        }

        //判断是否是测试数据
        infoLog('[internal:createForBatch] validator is_test start.');
          if ($is_test == CrawlResult::EFFECT_TEST) {
                $platformParams = [];
                $platformParams['result'] = [];
                $platformParams['is_test'] = $params['is_test'];
                foreach ($params['result'] as $value) {
                    $value['task_id'] = $params['task_id'];
                    $platformParams['result'][] = $value;
                }
                $platformData = formatPlarformData($platformParams);
                $platformResult = APIService::post('http://boss.lsjpic.com/api/live/put_craw_data', $platformData);
                if (!empty($platformResult)) {
                    infoLog('[internal:createForBatch] request platform API error', $platformData);
                    return resError(501, '数据发送异常');
                }
                return $this->resObjectGet($params['task_id'], 'crawl_result', $request->path());
        }
        infoLog('[internal:createForBatch] validator is_test end.');
        //url去重复
        infoLog('[internal:createForBatch] url removal start.');
        $formatData = [];
        foreach ($params['result'] as $value) {
            $result = APIService::baseGet('/internal/basic/crawl/result/search', ['task_id' => $params['task_id'], 'url' => $value['url']]);
            if ($result['status_code'] != 200) {
                errorLog('[createForBatch] request /internal/basic/crawl/result/search result error', $value);
                return response($responseDada['message'], $responseDada['status_code']);
            }
            if (empty($result['data'])) {
                // $value['format_data'] = $value;
                // $value['task_id'] = $params['task_id'];
                // $value['start_time'] = $params['start_time'];
                // $value['end_time'] = $params['end_time'];
                $newFormatData[] = $value;
            }
        }
        $params['result'] = $newFormatData;
        infoLog('[internal:createForBatch] url removal end.');

        if (empty($params['result'])) {
            infoLog('[internal::createForBatch] result empty.');
            return $this->resObjectGet([], 'crawl_result', $request->path());
        }
        infoLog('[internal::createForBatch] request internal/basic createForBatch start');
        $resultData = APIService::basePost('/internal/basic/crawl/results', $params, 'json');
        if ($resultData['status_code'] != 200) {
            errorLog('[createForBatch] request internal/basic createForBatch result error', $newFormatData);
            return response($resultData['message'], $resultData['status_code']);
        }
        infoLog('[internal::createForBatch] request internal/basic createForBatch end');




















        if (empty($params['data'])) {
            infoLog('[createForBatch] data empty!');
            return $this->resObjectGet([], 'crawl_result', $request->path());
        }
        $formatData = [];
        $is_test = $params['data'][0]['is_test'];
        $crawl_task_id = $params['data'][0]['crawl_task_id'];

        $taskParams['id'] = $crawl_task_id;
        $taskParams['last_job_at'] = date('Y-m-d H:i:s');
        $taskResult = APIService::internalPost('/internal/crawl/task/last_job_at', $taskParams, 'json');
        if ($taskResult['status_code'] != 200) {
            errorLog('[createForBatch] request /v1/crawl/task/last_job_at createForBatch result error', $taskParams);
            return response($taskResult['message'], $taskResult['status_code']);
        }

        foreach ($params['data'] as $crawlResult) {
            if (empty($crawlResult['data'])) {
                return $this->resObjectGet([], 'crawl_result', $request->path());
            }
            foreach ($crawlResult['data'] as $crawlResultValue) {
                $crawlResult['task_url'] = $crawlResultValue['url'];
                $crawlResult['format_data'] = $crawlResultValue['text'];
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
        $formatData['data'] = arrayRemovalDuplicate($formatData['data'], 'format_data');
        if (empty($formatData['data'])) {
            return $this->resObjectGet($formatData, 'formatData empty.', $request->path());
        }
        if (empty($is_test)) {
            $is_test = 1;
        }
        if ($is_test == CrawlResult::EFFECT_TEST) {
            $platformData = [];

            $platformData = formatPlarformData($formatData['data'], $is_test);
            //平台接口调用
            //dd($platformData);
            $platformResult = APIService::post('http://boss.jinse.com/api/live/put_craw_data', $platformData);
            if (!empty($platformResult)) {
                infoLog('[createForBatch] request platform API error', $platformData);
                return response('数据发送异常', 501);
            }
            return $this->resObjectGet($formatData, 'crawl_result', $request->path());
        }
        //去重复
        $newFormatData['data'] = [];
        foreach ($formatData['data'] as $formatValue) {
            $formatValue['data'] = null;
            $responseDada = APIService::baseGet('/internal/basic/crawl/result/is_task_exist', ['id' => $formatValue['crawl_task_id'], 'url' => $formatValue['task_url']]);
            if ($responseDada['status_code'] != 200) {
                errorLog('[createForBatch] request /internal/basic/crawl/result/is_task_exist result error', $formatValue);
                return response($responseDada['message'], $responseDada['status_code']);
            }
            if (empty($responseDada['data'])) {
                $newFormatData['data'][] = $formatValue;
            }
        }
        if (empty($newFormatData['data'])) {
            infoLog('[createForBatch] repeated data');
            return $this->resObjectGet([], 'crawl_result', $request->path());
        }
        infoLog('[createForBatch] request internal/basic createForBatch start');
        $resultData = APIService::basePost('/internal/basic/crawl/result/batch_result', $newFormatData, 'json');
        if ($resultData['status_code'] != 200) {
            errorLog('[createForBatch] request internal/basic createForBatch result error', $newFormatData);
            return response($resultData['message'], $resultData['status_code']);
        }
        $result = [];
        if ($resultData['data']) {
            $result = $resultData['data'];
            $platformData = [];
            $platformData = formatPlarformData($result, $is_test);
            //平台接口调用
            $platformResult = APIService::post(config('url.platform_url'), $platformData);
            if (!empty($platformResult)) {
                infoLog('[createForBatch] request platform API error', $platformData);
                return response('数据发送异常', 501);
            }

        }
        infoLog('[createForBatch] request internal/basic createForBatch end');
        infoLog('[createForBatch] end.');


        return $this->resObjectGet($result, 'crawl_result', $request->path());
    }

 }
