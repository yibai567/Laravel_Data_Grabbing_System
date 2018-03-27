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
            return $this->resObjectList($resData, 'crawl_result', $request->path());
        }

        // 数据唯一判断
        $newResult = [];
        $reportResult = ['is_test' => $params['is_test'], 'result' => []];

        foreach ($resData as $key => $value) {
            $md5Value = md5(json_encode($value, true));

            $resultDetail = APIService::basePost('/internal/basic/crawl/result/search', ['task_id' => $params['task_id'], 'md5_fields' => md5(json_encode($value, true))], 'json');
            if (empty($resultDetail)) {

                // 整理需要上报的结构
                $reportResult['result'][$key] = $value;
                $reportResult['result'][$key]['task_id'] => $params['task_id'];

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

 }
























