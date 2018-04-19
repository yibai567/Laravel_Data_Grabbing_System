<?php

namespace App\Http\Controllers\InternalAPI;

use App\Models\Item;
use App\Models\ItemRunLog;
use App\Models\QueueInfo;
use App\Services\InternalAPIService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Log;
use App\Services\ValidatorService;

/**
 * QueueInfoController
 * 任务运行日志
 *
 * @author zhangwencheng@jinse.com
 * @version 1.1
 * Date: 2018/04/12
 */
class QueueInfoController extends Controller
{
    /**
     * updateCurrentLength
     * 更新
     *
     * @param
     * @return array
     */
    public function updateCurrentLength(Request $request)
    {
        $result = [];
        $queues = QueueInfo::all();

        if (!empty($queues)) {
            foreach ($queues as $queue) {
                $currentLength = Redis::connection($queue->db)->lLen($queue->name);

                $queue->current_length = $currentLength;
                $queue->save();
            }

            $result = $queues->toArray();
        }

        return $this->resObjectGet($result, 'queue_info', $request->path());
    }

    /**
     * getJob
     * 获取指定队列的任务
     *
     * @param id
     * @return array
     */
    public function getJob(Request $request)
    {
        $params = $request->all();
        ValidatorService::check($params, [
            'id' => 'required|integer|min:1|max:14',
        ]);

        $queueInfo = QueueInfo::find($params['id']);
        if (empty($queueInfo)) {
            return $this->resError(405, '指定队列不存在');
        }

        $job = Redis::connection($queueInfo->db)->rPop($queueInfo->name);
        $job = json_decode($job);

        if (empty($job)) {
            $job = [];
        }

        return $this->resObjectGet($job, 'queue_info', $request->path());
    }

    /**
     * createJob
     * 创建任务
     *
     * @param Request $request
     */
    public function createJob(Request $request)
    {
        $params = $request->all();
        Log::debug('[createJob] ' . '创建任务队列' . $params);
        ValidatorService::check($params, [
            'id' => 'required|integer|min:1|max:14',
            'item_id' => 'required|integer',
            'item_run_log_id' => 'nullable|integer',
        ]);

        $item = Item::find($params['item_id']);
        Log::debug('[createJob] ' . '获取任务详情' . $item);

        if (empty($item)) {
            Log::debug('[createJob] ' . '指定Item不存在!');
            return $this->resError(405, '指定Item不存在!');
        }

        $queueInfo = QueueInfo::find($params['id']);
        if (empty($queueInfo)) {
            Log::debug('[createJob] ' . '指定队列不存在!');
            return $this->resError(405, '指定队列不存在!');
        }

        if (strpos($queueInfo->name, 'test')) {
            $type = ItemRunLog::TYPE_TEST;
        } else {
            $type = ItemRunLog::TYPE_PRO;
        }

        // 判断参数是否有item_run_log_id，如果有不重新申请
        if (empty($params['item_run_log_id'])) {
            $res = [
                'item_id' => $item->id,
                'type' => $type,
                'status' => ItemRunLog::STATUS_RUNNING,
                'start_at' => Carbon::now()->toDateTimeString(),
            ];

            $itemRunLog = InternalAPIService::post('/item_run_log', $res);
            $itemRunLogId = $itemRunLog['id'];
        } else {
            $itemRunLogId = $params['item_run_log_id'];
        }

        $data = [
            'item_id' => $item->id,
            'item_run_log_id' => $itemRunLogId,
            'resource_url' => $item->resource_url,
            'short_content_selector' => $item->short_content_selector,
            'long_content_selector' => $item->long_content_selector,
            'row_selector' => $item->row_selector,
            'header' => $item->header
        ];

        Redis::connection($queueInfo->db)
            ->lpush($queueInfo->name, json_encode($data));

        return $this->resObjectGet($data, 'queue_info', $request->path());
    }
}
