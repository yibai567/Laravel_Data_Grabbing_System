<?php

namespace App\Http\Controllers\InternalAPI;

use Illuminate\Http\Request;
use App\Models\CrawlResult;
use App\Models\CrawlTask;
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

        infoLog('[internal:createForBatch] params validator start.', $params);
        if ($validator->fails()) {
            $errors = $validator->errors();

            foreach ($errors->all() as $value) {
                errorLog('[internal:createForBatch] params validator fail.', $value);
                return $this->resError(401, $value);
            }
        }
        infoLog('[internal:createForBatch] params validator end.');

        //任务详情
        infoLog('[internal:createForBatch] task detail start.');
        $taskDetail = $this->__taskDetail($params['task_id']);

        if (empty($taskDetail)) {
            return $this->resError(401, '任务不存在');
        }
        infoLog('[internal:createForBatch] task detail end.');

        //更新任务表last_job_at字段
        infoLog('[internal:createForBatch] update t_crawl_task last_job_at start.');
        $taskParams = [];
        $taskParams['id'] = $params['task_id'];
        $taskParams['last_job_at'] = date('Y-m-d H:i:s');

        if ($taskDetail['status'] == CrawlTask::IS_INIT || $taskDetail['status'] == CrawlTask::IS_TEST_ERROR) {
            $taskParams['status'] = CrawlTask::IS_TEST_SUCCESS;
        }

        $taskParams['test_time'] = date('Y-m-d H:i:s');
        $taskParams['test_result'] = json_encode($params['result']);

        $this->__updateTask($taskParams);
        infoLog('[internal:createForBatch] update t_crawl_task last_job_at end.');

        if (empty($params['result']) || empty($params['result']['a'])) {
            infoLog('[internal:createForBatch] result empty!');
            return $this->resObjectGet($params, 'crawl_result', $request->path());
        }

        //数据去重
        infoLog('[internal:createForBatch] params filter start.');

        $params['result'] = $this->__dataFilter($params['result']['a'], $taskDetail['resource_url'], $params['task_id']);

        if (empty($params['result'])) {
            infoLog('[internal::createForBatch] result empty.');
            return $this->resObjectGet($params['task_id'], 'crawl_result', $request->path());
        }

        infoLog('[internal:createForBatch] params filter end.');

        //判断是否是测试数据
        infoLog('[internal:createForBatch] validator is_test start.');
        if ($params['is_test'] == CrawlResult::EFFECT_TEST) {
                if ($this->__platformAPI($result) === false) {
                    return $this->resError(502, '调用外部接口失败');
                }
                return $this->resObjectGet($params, 'crawl_result', $request->path());
        } else {
            $params['is_test'] = 0;
        }

        infoLog('[internal:createForBatch] validator is_test end.');
        $resultData = APIService::basePost('/internal/basic/crawl/results', $params, 'json');

        if ($resultData['status_code'] != 200) {
            errorLog('[createForBatch] request internal/basic createForBatch result error');
            return response($resultData['message'], $resultData['status_code']);
        }

        infoLog('[internal::createForBatch] request internal/basic createForBatch end');

        $result = [];

        if ($resultData['data']) {
            $result = $resultData['data'];
            if ($this->__platformAPI($result) === false) {
                return $this->resError(502, '调用外部接口失败');
            }
        }

        return $this->resObjectGet($result, 'crawl_result', $request->path());
    }

    /**
     * 请求平台接口
     * @param Request $request
    */
    private function __platformAPI($params)
    {
        $platformParams = [];
        $platformParams['result'] = [];
        $platformParams['is_test'] = $params['is_test'];

        foreach ($params['result'] as $key => $value) {
            $newArr['title'] = $value['text'];
            $newArr['url'] = $value['url'];
            $newArr['task_id'] = $params['task_id'];
            $platformParams['result'][] = $newArr;
        }
        $platformData = sign($platformParams);
        infoLog('[url.platform_url] '. config('url.platform_url'));
        $platformResult = APIService::post(config('url.platform_url'), $platformData);

        if ($platformResult === false) {
            infoLog('[internal:createForBatch] request platform API error', $platformData);
            return false;
        }
        return true;
    }

    /**
     * 获取任务详情
     * @param Request $request
     * $taskId 任务id
    */
    private function __taskDetail($taskId)
    {
        $taskDetail = APIService::baseGet('/internal/basic/crawl/task', ['id' => $taskId], 'json');

        if ($taskDetail['status_code'] != 200) {
            return $this->resError(401, 'get task detail error!');
        }

        $result = [];
        if (!empty($taskDetail)) {
            $result = $taskDetail['data'];
        }

        return $result;
    }

    /**
     * 修改任务信息
     * @param Request $request
     * $taskParams 需要修改的任务参数
    */
    private function __updateTask($taskParams)
    {
        $taskResult = APIService::internalPost('/internal/basic/crawl/task/update', $taskParams, 'json');

        if ($taskResult['status_code'] != 200) {
            errorLog('[internal:createForBatch] update t_crawl_task last_job_at error', $taskParams);
            return $this->resError($taskResult['status_code'], $taskResult['message']);
        }
    }

    /**
     * 数据去重
     * @param Request $request
     * $data(原始数据) $taskUrl(任务url) $taskId(任务id)
    */
    private function __dataFilter($data, $taskUrl, $taskId)
    {
        $formatData = [];

        foreach ($data as $value) {
            if (empty($value['text']) || $value['text'] == 'undefined' || empty($value['url']) || $value['url'] == 'undefined' || $value['url'] == 'javascript:;') {
                    continue;
               }

            $result = APIService::basePost('/internal/basic/crawl/result/search', ['task_id' => $taskId, 'original_data' => md5(json_encode($value))], 'json');

            if ($result['status_code'] != 200) {
                errorLog('[internal:createForBatch] request /internal/basic/crawl/result/search result error', $value);
                continue;
            }

            if (empty($result['data'])) {
                $strOne = substr($value['url'], 0,1);
                if ($strOne == '/') {
                    $strTwo = substr($value['url'], 1,1);
                    if ($strTwo != '/') {
                        $value['url'] = $taskUrl . $value['url'];
                    }
                }
                $formatData[] = $value;
            }
        }
        return $formatData;
    }
 }
