<?php

namespace App\Http\Controllers\InternalAPI\Basic;

use DB;
use App\Http\Requests\CrawlResultCreateRequest;
use App\Models\CrawlResult;
use App\Http\Controllers\InternalAPI\Controller;

class CrawlResultController extends Controller
{
    /**
     * 返回爬虫结果数据接口
     * @param CrawlResultCreateRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(CrawlResultCreateRequest $request)
    {
        $data = $request->postFillData();
        if (empty($data)) {
            return response('返回数据为空', 404);
        }
        if ($this->isTaskExist($data['crawl_task_id'], $data['task_url'])) {
            return response('任务已存在');
        }
        $result = CrawlResult::create($data);
        return $this->resObjectGet($result, 'crawl_result', $request->path());
    }

    /**
     * 批量插入数据
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function pushByList(Request $request)
    {
        $data = $request->get('data');
        $items = [];
        DB::beginTransaction();
        foreach ($data as $item) {
            if (!$this->isTaskExist($item['crawl_task_id'], $item['task_url'])) {
                $items[] = $item;
            }
            $items[] = [
                'crawl_task_id' => $item['crawl_task_id'],
                'original_data' => $item['original_data'],
                'task_start_time' => $item['task_start_time'],
                'task_end_time' => $item['task_end_time'],
                'task_url' => $item['task_url'],
                'format_data' => $item['format_data'],
                'setting_selectors' => $item['setting_selectors'],
                'setting_keywords' => $item['setting_keywords'],
                'setting_data_type' => $item['setting_data_type'],
                'status' => CrawlResult::IS_UNTREATED,
            ];
        }
        $result = CrawlResult::insert($items);
        DB::commit();
        if (!$result) {
            DB::rollBack();
            return response('更新失败', 402);
        }
        return $this->resObjectGet($result, 'crawl_result', $request->path());
    }

    /**
     * 根据任务id及任务url判断任务是否存在
     * @param $taskId
     * @param $taskUrl
     * @return bool
     */
    protected function isTaskExist($taskId, $taskUrl)
    {
        $crawlResult = CrawlResult::where(['task_url' => $taskId, 'crawl_task_id' => $taskUrl])
            ->first();
        if ($crawlResult) {
            return true;
        }
        return false;
    }
}
