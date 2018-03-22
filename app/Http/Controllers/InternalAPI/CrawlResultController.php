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
        $taskParams = [];
        $taskParams['id'] = $params['task_id'];
        $taskParams['last_job_at'] = date('Y-m-d H:i:s');
        $taskResult = APIService::internalPost('/internal/basic/crawl/task/update', $taskParams, 'json');
        if ($taskResult['status_code'] != 200) {
            errorLog('[internal:createForBatch] update t_crawl_task last_job_at error', $taskParams);
            return resError($taskResult['status_code'], $taskResult['message']);
        }
        infoLog('[internal:createForBatch] update t_crawl_task last_job_at end.');

        if (empty($params['result'])) {
            infoLog('[internal:createForBatch] result empty!');
            return $this->resObjectGet($params, 'crawl_result', $request->path());
        }
        //判断是否是测试数据
        infoLog('[internal:createForBatch] validator is_test start.');
        if ($params['is_test'] == CrawlResult::EFFECT_TEST) {
                $this->__platformAPI($params);
                return $this->resObjectGet($params, 'crawl_result', $request->path());
        } else {
            $params['is_test'] = 0;
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

        $result = [];
        if ($resultData['data']) {
            $result = $resultData['data'];
            $this->__platformAPI($result);
        }
        return $this->resObjectGet($result, 'crawl_result', $request->path());
    }

    private function __platformAPI($params)
    {
        $platformParams = [];
        $platformParams['result'] = [];
        $platformParams['is_test'] = $params['is_test'];
        foreach ($params['result'] as $value) {
            $value['task_id'] = $params['task_id'];
            $platformParams['result'][] = $value;
        }
        $platformData = sign($platformParams);
        $platformResult = APIService::post(config('url.platform_url'), $platformData);
        if (!empty($platformResult)) {
            infoLog('[internal:createForBatch] request platform API error', $platformData);
            return resError(501, '数据发送异常');
        }
    }

 }
