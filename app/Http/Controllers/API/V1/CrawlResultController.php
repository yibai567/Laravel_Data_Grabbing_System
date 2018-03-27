<?php

namespace App\Http\Controllers\API\V1;

use Illuminate\Http\Request;
use Log;
use App\Services\ValidatorService;
use App\Services\APIService;
use App\Models\CrawlTask;

/**
 * CrawlResultController
 * 任务抓取结果控制器
 * @author huangxingxing@jinse.com
 * @version 1.1
 * Date: 2018/03/25
 */
class CrawlResultController extends Controller
{
    /**
     * createForBatch
     * 保存抓取结果
     *
     * @param task_id (抓取任务ID)
     * @param is_test (是否是测试数据) 1测试|2插入
     * @param start_time (开始时间)
     * @param end_time (结束时间)
     * @param result (抓取结果)
     * @return array
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

        $data = APIService::internalPost('/internal/crawl/results', $params);

        return $this->resObjectGet($data, 'crawl_result', $request->path());
    }

    /**
     * dispatch
     * 结果分发
     *
     * @param task_id (抓取任务ID)
     * @param is_test (是否是测试数据) 1测试|2插入
     * @param start_time (开始时间)
     * @param end_time (结束时间)
     * @param result (抓取结果)
     * @return array
     */
    public function dispatch(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'task_id' => 'required|integer|min:1|max:99999999',
            'is_test' => 'integer|nullable',
            'start_time' => 'date|nullable',
            'end_time' => 'date|nullable',
            'result' => 'array|nullable',
        ]);

        if ($params['is_test'] == CrawlResult::IS_TEST_TRUE) {
            $params['is_test'] = CrawlResult::IS_TEST_TRUE;
        } else {
            $params['is_test'] = CrawlResult::IS_TEST_FALSE;
        }

        // 获取任务信息
        $task = APIService::internalPost('/internal/crawl/task', ['id' => $params['task_id']]);
        if (empty($task)) {
            throw new \Dingo\Api\Exception\ResourceException("task is not found");
        }

        // 测试结果需要更新
        if ($params['is_test'] == CrawlResult::IS_TEST_TRUE) {
            try {
                APIService::internalPost('/internal/crawl/result/test', ['task_id' => $params['task_id'], 'result' => $params['result']], 'json');
            } catch (\Dingo\Api\Exception\InternalHttpException $e) {
                throw new \Dingo\Api\Exception\ResourceException("update test result fail");
            }
        }

        // 按任务资源数据的类型进行分发
        switch ($task['resource_type']) {
            case CrawlTask::RESOURCE_TYPE_HTML:
                $result = APIService::internalPost('/internal/crawl/results/html', $params);
                break;

            case CrawlTask::RESOURCE_TYPE_JSON:
                $result = APIService::internalPost('/internal/crawl/results/json', $params);
                break;

            default:
                throw new \Dingo\Api\Exception\ResourceException("invalid task resource type");
                break;
        }

        return $this->resObjectGet($result, 'crawl_result', $request->path());
    }

}























