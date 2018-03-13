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
     * 支持单条，批量插入数据
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createByBatch(Request $request)
    {
        $params = $request->all();
        infoLog("[createByBatch] start.");
        $validator = Validator::make($params, [
            'crawl_task_id' => 'numeric|nullable',
            'task_start_time' => 'date|nullable',
            'task_end_time' => 'date|nullable',
            'original_data' => 'nullable',
            'format_data' => 'nullable',
            'task_url' => 'string|nullable',
            'setting_selectors' => 'string|nullable',
            'setting_keywords' => 'string|nullable',
            'setting_data_type' => 'numeric|nullable',
        ]);
        infoLog('[createByBatch] params validator start', $params);
        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                errorLog('[createByBatch] params validator fail', $value);
                return response($value, 401);
            }
        }
        infoLog('[params validator] end.');
        $result = [];

        if (empty($params['format_data'])) {
            infoLog('[createByBatch] format_data empty!');
            return $this->resObjectGet($result, 'crawl_result', $request->path());
        }
        $items = [];
        foreach ($params['format_data'] as $item) {
            if (!$this->isTaskExist($params['crawl_task_id'], $item['url'])) {
                $params['task_url'] = $item['url'];
                $params['format_data'] = json_encode($item);
                $params['original_data'] = json_encode($params['original_data']);
                $params['status'] = CrawlResult::IS_UNTREATED;
                $items[] = $params;
            }
        }

        if (empty($items)) {
            infoLog('[createByBatch] items empty!');
            return $this->resObjectGet($result, 'crawl_result', $request->path());
        }

        infoLog('[createByBatch] insert start.', $items);
        $result = CrawlResult::insert($items);
        if (!$result) {
            errorLog('[createByBatch] insert fail.', $items);
            return response('插入失败', 402);
        }
        infoLog('[createByBatch] insert end.', $result);
        infoLog('[createByBatch] end.', $result);
        return $this->resObjectGet($result, 'crawl_result', $request->path());
    }

    /**
     * 根据任务id及任务url判断任务是否存在
     * @param $taskId
     * @param $taskUrl
     * @return bool
     */
    private function isTaskExist($taskId, $taskUrl)
    {
        $crawlResult = CrawlResult::where(['task_url' => $taskUrl, 'crawl_task_id' => $taskId])
            ->first();
        if ($crawlResult) {
            return true;
        }
        return false;
    }
}
