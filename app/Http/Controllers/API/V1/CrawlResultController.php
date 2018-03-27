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
     * Dispatch
     * 结果分发
     *
     * @param task_id (抓取任务ID)
     * @param is_test (是否是测试数据) 1测试|2插入
     * @param start_time (开始时间)
     * @param end_time (结束时间)
     * @param result (抓取结果)
     * @return array
     */
    public function Dispatch(Request $request)
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























