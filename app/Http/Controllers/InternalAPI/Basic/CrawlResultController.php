<?php

namespace App\Http\Controllers\InternalAPI\Basic;

use DB;
use App\Http\Requests\CrawlResultCreateRequest;
use App\Models\CrawlResult;
use App\Http\Controllers\InternalAPI\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CrawlResultController extends Controller
{
    /**
     * 返回爬虫结果数据接口
     * @param CrawlResultCreateRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'crawl_task_id' => 'string|nullable',
            'original_data' => 'string|nullable',
            'task_start_time' => 'date|nullable',
            'task_end_time' => 'date|nullable',
            'task_url' => 'string|nullable',
            'format_data' => 'string|nullable',
            'setting_selectors' => 'string|nullable',
            'setting_keywords' => 'string|nullable',
            'setting_data_type' => 'string|nullable',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                return response($value, 401);
            }
        }
        $data = [
            'crawl_task_id' => $request->crawl_task_id,
            'original_data' => $request->original_data,
            'task_start_time' => $request->task_start_time,
            'task_end_time' => $request->task_end_time,
            'task_url' => $request->task_url,
            'format_data' => $request->format_data,
            'setting_selectors' => $request->setting_selectors,
            'setting_keywords' => $request->setting_keywords,
            'setting_data_type' => $request->setting_data_type,
        ];
        if (empty($data)) {
            return $this->resObjectGet($data, 'crawl_result', $request->path());
        }
        if ($this->isTaskExist($data['crawl_task_id'], $data['task_url'])) {
            return $this->resObjectGet($data['crawl_task_id'], 'crawl_result', $request->path());
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
        $crawlResult = CrawlResult::where(['task_url' => $taskUrl, 'crawl_task_id' => $taskId])
            ->first();
        if ($crawlResult) {
            return true;
        }
        return false;
    }
}
