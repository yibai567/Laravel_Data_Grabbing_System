<?php

namespace App\Http\Controllers\InternalAPI;

use Illuminate\Http\Request;
use App\Models\CrawlResult;
use App\Models\CrawlTask;
use App\Services\ValidatorService;
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
        $params = $request->all();

        ValidatorService::check($params, [
            'task_id' => 'required|integer',
            'is_test' => 'integer|nullable',
            'start_time' => 'date|nullable',
            'end_time' => 'date|nullable',
            'result' => 'nullable',
        ]);
        //任务详情
        $taskDetail = $this->__taskDetail($params['task_id']);

        //更新任务表last_job_at字段
        $taskParams = [];
        $taskParams['id'] = $params['task_id'];
        $taskParams['last_job_at'] = date('Y-m-d H:i:s');

        if ($taskDetail['status'] == CrawlTask::IS_INIT || $taskDetail['status'] == CrawlTask::IS_TEST_ERROR) {
            $taskParams['status'] = CrawlTask::IS_TEST_SUCCESS;
        } else {
            $taskParams['status'] = $taskDetail['status'];
        }

        $taskParams['test_time'] = date('Y-m-d H:i:s');
        $taskParams['test_result'] = json_encode($params['result']);

        APIService::internalPost('/internal/basic/crawl/task/update', $taskParams, 'json');

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
            $this->__platformAPI($params);
            return $this->resObjectGet($params, 'crawl_result', $request->path());
        } else {
            $params['is_test'] = 0;
        }

        infoLog('[internal:createForBatch] validator is_test end.');

        $resultData = APIService::basePost('/internal/basic/crawl/results', $params, 'json');

        $result = [];
        if ($resultData) {
            $result = $resultData;
            $this->__platformAPI($result);
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
        APIService::post(config('url.platform_url'), $platformData);
        return;
    }

    /**
     * 获取任务详情
     * @param Request $request
     * $taskId 任务id
    */
    private function __taskDetail($taskId)
    {
        $taskDetail = APIService::baseGet('/internal/basic/crawl/task', ['id' => $taskId], 'json');

        if (!empty($taskDetail)) {
            return $taskDetail;
        } else {
            returnError(401, '任务不存在');
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
        $urlArr = parse_url($taskUrl);
        $url = '';
        if (isset($urlArr['scheme']) && isset($urlArr['host'])) {
            $url = $urlArr['scheme'] . '://' . $urlArr['host'] . '/';
        }

        if (empty($url)) {
            returnError(401, '参数错误');
        }
        foreach ($data as $value) {
            if (empty($value['text']) || $value['text'] == 'undefined' || empty($value['url']) || $value['url'] == 'undefined' || $value['url'] == 'javascript:;') {
                    continue;
               }

            if (substr($value['url'], 0,1) == '/' && substr($value['url'], 1,1) != '/') {                                       $value['url'] = $url . $value['url'];
            }

            $result = APIService::basePost('/internal/basic/crawl/result/search', ['task_id' => $taskId, 'original_data' => md5(json_encode($value))], 'json');
            if (empty($result)) {
                $formatData[] = $value;
            }
        }
        return $formatData;
    }
 }
