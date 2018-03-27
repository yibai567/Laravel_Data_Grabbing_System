<?php

namespace App\Http\Controllers\InternalAPI;

use Illuminate\Http\Request;
use App\Models\CrawlResult;
use App\Models\CrawlTask;
use App\Services\ValidatorService;
use App\Services\APIService;


class CrawlResultV2Controller extends Controller
{
    /**
     * saveAllToJson
     * 批量插入数据, 对于接口请求的结果
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
    */
    public function saveAllToJson(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'task_id' => 'required|integer',
            'is_test' => 'integer|nullable',
            'start_time' => 'date|nullable',
            'end_time' => 'date|nullable',
            'result' => 'nullable',
        ]);

        // 获取任务信息
        $task = APIService::internalPost('/internal/crawl/task', ['id' => $params['task_id']]);
        if (empty($task)) {
            throw new \Dingo\Api\Exception\ResourceException("task is not found");
        }

        // 获取 api_fields
        if (empty($task['api_fields'])) {
            throw new \Dingo\Api\Exception\ResourceException("invalid api fields config");
        }

        // 格式化抓取结果
        $resData = [];
        $resultList = $params['result'];

        // 分析列表数据所在位置，并获取
        if (empty($task['api_fields']['filter'])) {
            $resultListkeys = explode('-', $task['api_fields']['filter']);

            foreach ($resultListkeys as $resultListkey) {
                $resultList = $resultList[$resultListkey];
            }
        }

        if (empty($resultList)) {
            throw new \Dingo\Api\Exception\ResourceException("not found result list");
        }

        // 按需重新组装结果
        foreach ($resultList as $rowkey => $row) {
            foreach ($task['api_fields'] as $key => $value) {
                if ($key == 'filter') {
                    continue;
                }

                $resData[$rowKey][$key] = $row[$value];
            }
        }

        if (empty($resData)) {
            return $this->resObjectList([], 'crawl_result', $request->path());
        }

        // 数据唯一判断
        $filterResult = $this->__filterResult($resData, $params['is_test'], $params['taskId']);
        $newResult = $filterResult['newResult'];
        $reportResult = $filterResult['reportResult'];

        if (empty($newResult)) {
            return $this->resObjectList([], 'crawl_result', $request->path());
        }

        // 上报结果
        try {
            APIService::post(config('url.platform_url'), sign($reportResult));
        } catch (\Exception $e) {
            foreach ($newResult as $key => $value) {
                $newResult[$key]['status'] = CrawlResult::IS_REPORT_ERROR,
            }
        }

        // 保存数据
        $saveResult = CrawlResult::insert($newResult);

        return $this->resObjectList($saveResult, 'crawl_result', $request->path());
    }

    /**
     * saveAllToHtml
     * 批量插入数据, 对于页面请求的结果
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
    */
    public function saveAllToHtml(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'task_id' => 'required|integer',
            'is_test' => 'integer|nullable',
            'start_time' => 'date|nullable',
            'end_time' => 'date|nullable',
            'result' => 'nullable',
        ]);

        // 获取任务信息
        $task = APIService::internalPost('/internal/crawl/task', ['id' => $params['task_id']]);
        if (empty($task)) {
            throw new \Dingo\Api\Exception\ResourceException("task is not found");
        }

        // 格式化抓取结果
        $resData = [];
        $resultList = $params['result'];
        if (empty($resultList)) {
            throw new \Dingo\Api\Exception\ResourceException("not found result list");
        }

        // 按需重新组装结果
        foreach ($resultList as $rowkey => $row) {
            if (empty($row)) {
                continue;
            }

            foreach ($row as $key => $value) {
               $resData[$key][$rowkey] = $value;
            }
        }

        // $resData = [['title' => 'block chain', 'url' => ‘http://xxxx’]]
        if (empty($resData)) {
            return $this->resObjectList([], 'crawl_result', $request->path());
        }

        // 数据唯一判断
        $filterResult = $this->__filterResult($resData, $params['is_test'], $params['taskId']);
        $newResult = $filterResult['newResult'];
        $reportResult = $filterResult['reportResult'];

        if (empty($newResult)) {
            return $this->resObjectList([], 'crawl_result', $request->path());
        }

        // 上报结果
        try {
            APIService::post(config('url.platform_url'), sign($reportResult));
        } catch (\Exception $e) {
            // TODO LOG
            foreach ($newResult as $key => $value) {
                $newResult[$key]['status'] = CrawlResult::IS_REPORT_ERROR,
            }
        }

        // 保存数据
        $saveResult = CrawlResult::insert($newResult);

        return $this->resObjectList($saveResult, 'crawl_result', $request->path());
    }

    /**
     * saveToTest
     * 测试数据处理
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
    */
    public function saveToTest(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'task_id' => 'required|integer',
            'result' => 'nullable',
        ]);

        // 获取任务信息
        $task = APIService::internalPost('/internal/crawl/task', ['id' => $params['task_id']]);
        if (empty($task)) {
            throw new \Dingo\Api\Exception\ResourceException("task is not found");
        }

        // 测试结果需要更新
        $updateParams = [
            'id' => $params['task_id'],
            'last_job_at' => date('Y-m-d H:i:s'),
            'test_time' => date('Y-m-d H:i:s'),
            'test_result' => json_encode($params['result'], true),
        ];

        // 目前只有成功测试的数据才会调用接口，TODO 失败情况的处理
        if ($task['status'] == CrawlTask::IS_INIT || $task['status'] == CrawlTask::IS_TEST_ERROR) {
            $updateParams['status'] = CrawlTask::IS_TEST_SUCCESS;
        }

        try {
            APIService::internalPost('/internal/basic/crawl/task/update', $updateParams, 'json');
        } catch (\Dingo\Api\Exception\InternalHttpException $e) {
            throw new \Dingo\Api\Exception\ResourceException("update test result fail");
        }

        // 上报结果
        $reportResult = ['is_test' => 1, 'result' => $params['result']];
        try {
            APIService::post(config('url.platform_url'), sign($reportResult));
        } catch (\Exception $e) {
            // TODO LOG
        }

        return $this->resObjectList($saveResult, 'crawl_result', $request->path());
    }

    /**
     * __filterResult
     * 获取任务详情
     *
     * @param Request $request
     * @return array
    */
    private function __filterResult($data, $isTest, $taskId)
    {
        $newResult = [];
        $reportResult = ['is_test' => $isTest, 'result' => []];

        foreach ($data as $key => $value) {
            $md5Value = md5(json_encode($value, true));

            $resultDetail = APIService::basePost('/internal/basic/crawl/result/search', ['task_id' => $params['task_id'], 'md5_fields' => $md5Value], 'json');
            if (empty($resultDetail)) {

                // 整理需要上报的结构
                $reportResult['result'][$key] = $value;
                $reportResult['result'][$key]['task_id'] => $taskId;

                // 整理需要保存的结构
                $newResult[$key] = [
                    'fields' => $value,
                    'md5_fields' => $md5Value,
                    'task_id' => $params['task_id'],
                    'start_time' => $params['start_time'],
                    'end_time' => $params['end_time',
                    'status' => CrawlResult::IS_UNTREATED,
                ];
            }
        }

        return ['newResult' => $newResult, 'reportResult' => $reportResult];
    }

 }
























