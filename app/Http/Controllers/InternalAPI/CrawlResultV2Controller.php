<?php

namespace App\Http\Controllers\InternalAPI;

use Illuminate\Http\Request;
use App\Models\CrawlResultV2 as CrawlResult;
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
        $task = APIService::baseGet('/internal/basic/crawl/task?id=' . $params['task_id']);
        if (empty($task)) {
            throw new \Dingo\Api\Exception\ResourceException("task is not found");
        }
        // 获取 api_fields
        if (empty($task['api_fields'])) {
            throw new \Dingo\Api\Exception\ResourceException("invalid api fields config");
        }

        // 格式化抓取结果
        $resData = $this->__formatResultToJson($task, $params);

        if (empty($resData)) {
            return $this->resObjectList([], 'crawl_result', $request->path());
        }
        // 数据唯一判断
        $filterResult = $this->__filterResult($resData, $params['is_test'], $params);
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
                $newResult[$key]['status'] = CrawlResult::IS_REPORT_ERROR;
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
         // 获取任务信息
        $task = APIService::baseGet('/internal/basic/crawl/task?id=' . $params['task_id']);
        if (empty($task)) {
            throw new \Dingo\Api\Exception\ResourceException("task is not found");
        }

        // 格式化抓取结果 $resData = [['title' => 'block chain', 'url' => ‘http://xxxx’]]
        $resData = $this->__formatResultToHtml($task, $params);

        if (empty($resData)) {
            return $this->resObjectList([], 'crawl_result', $request->path());
        }
        // 数据唯一判断
        $filterResult = $this->__filterResult($resData, $params['is_test'], $params);
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
                $newResult[$key]['status'] = CrawlResult::IS_REPORT_ERROR;
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
        $task = APIService::baseGet('/internal/basic/crawl/task?id=' . $params['task_id']);
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
        } else {
            $updateParams['status'] = $task['status'];
        }

        try {
            APIService::internalPost('/internal/basic/crawl/task/update', $updateParams, 'json');
        } catch (\Dingo\Api\Exception\InternalHttpException $e) {
            throw new \Dingo\Api\Exception\ResourceException("update test result fail");
        }

        // 上报结果 格式化
        if ($task['resource_type'] == CrawlTask::RESOURCE_TYPE_JSON) {
            $resData = $this->__formatResultToJson($task, $params);
        } else {
            $resData = $this->__formatResultToHtml($task, $params);
        }

        if (empty($resData)) {
            return $this->resObjectList([], 'crawl_result', $request->path());
        }

        // 上报结果
        $reportResult = ['is_test' => 1, 'result' => $resData];

        try {
            APIService::post(config('url.platform_url'), sign($reportResult));
        } catch (\Exception $e) {
            // TODO LOG
        }

        return $this->resObjectList($saveResult, 'crawl_result', $request->path());
    }

    /**
     * __formatResultToJson
     * json 数据格式化
     *
     * @param Request $request
     * @return array
    */
    private function __formatResultToJson($task, $params)
    {
        $resData = [];
        $resultList = $params['result'];
        $task['api_fields'] = json_decode($task['api_fields'], true);

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
            $rowData = [];
            foreach ($task['api_fields'] as $key => $value) {
                if ($key == 'filter') {
                    continue;
                }

                $valueFilter = $this->__formatURLAndTitle($row[$value], $task, $key);
                if ($valueFilter === false) {
                    continue;
                }

                $rowData[$key] = $valueFilter;
                $rowData['task_id'] = $task['id'];
            }

            $resData[] = $rowData;
        }

        return  $resData;
    }

    /**
     * __formatResultToHtml
     * html 数据格式化
     *
     * @param Request $request
     * @return array
    */
    private function __formatResultToHtml($task, $params)
    {
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
                $valueFilter = $this->__formatURLAndTitle($value, $task, $rowkey);
                if ($valueFilter === false) {
                    continue;
                }

               $resData[$key][$rowkey] = $valueFilter;
               $resData[$key]['task_id'] = $task['id'];
            }
        }

        return $resData;
    }

    /**
     * __formatURLAndTitle
     * 格式化 空地址 空 title
     *
     * @param Request $request
     * @return array
    */
    private function __formatURLAndTitle($valueFilter, $task, $key)
    {
        if ($key == 'url') {
            if (empty($valueFilter) || $valueFilter == 'undefined' || $valueFilter == 'javascript:;') {
                return false;
            }

            $url = $valueFilter;
            $httpPre = substr($valueFilter, 0, 4);
            $urlArr = parse_url($task['resource_url']);
            if ($httpPre != 'http:') {
                if (substr($valueFilter, 0, 2) == '//') {
                    $url = $urlArr['scheme'] . ':';
                } else {

                    if (substr($valueFilter, 0, 1) == '/') {
                        $url = $urlArr['scheme'] . '://' . $urlArr['host'];

                    } else {
                        $url = $urlArr['scheme'] . '://' . $urlArr['host'] . '/';
                    }
                }

            }

           $valueFilter = $url . $valueFilter;
        }

        if ($key == 'title') {
            if (empty($valueFilter) || $valueFilter == 'undefined') {
                    return false;
               }
        }

        return trim($valueFilter);
    }

    /**
     * __filterResult
     * 获取任务详情
     *
     * @param Request $request
     * @return array
    */
    private function __filterResult($data, $isTest, $params)
    {
        $newResult = [];
        $reportResult = ['is_test' => $isTest, 'result' => []];
        if ($reportResult['is_test'] == CrawlResult::IS_TEST_FALSE) {
            $reportResult['is_test'] = false;
        } else {
            $reportResult['is_test'] = true;
        }

        $currentTime = date('Y-m-d H:i:s');
        foreach ($data as $key => $value) {
            $md5Value = md5(json_encode($value, true));

            $resultDetail = APIService::basePost('/internal/basic/crawl/result/search_v2', ['task_id' => $params['task_id'], 'md5_fields' => $md5Value], 'json');

            if (empty($resultDetail)) {

                // 整理需要上报的结构
                $reportResult['result'][$key] = $value;
                $reportResult['result'][$key]['task_id'] = $params['task_id'];

                // 整理需要保存的结构
                $newResult[$key] = [
                    'fields' => json_encode($value, true),
                    'md5_fields' => $md5Value,
                    'crawl_task_id' => $params['task_id'],
                    'start_time' => $params['start_time'],
                    'end_time' => $params['end_time'],
                    'status' => CrawlResult::IS_UNTREATED,
                    'created_at' => $currentTime,
                    'updated_at' => $currentTime,

                ];
            }
        }

        return ['newResult' => $newResult, 'reportResult' => $reportResult];
    }

 }