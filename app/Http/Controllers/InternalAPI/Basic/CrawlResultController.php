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
            'data' => 'nullable',
        ]);
        infoLog('[createByBatch] params validator start');
        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                errorLog('[createByBatch] params validator fail', $value);
                return response($value, 401);
            }
        }
        infoLog('[params validator] end.');
        $result = [];

        if (empty($params['data'])) {
            infoLog('[createByBatch] data empty!');
            return $this->resObjectGet($result, 'crawl_result', $request->path());
        }
        $items = [];
        foreach ($params['data'] as $item) {

            if (!$this->isTaskExist($item['crawl_task_id'], $item['task_url'])) {
                $item['format_data'] = json_encode($item['format_data']);
                $item['status'] = CrawlResult::IS_PROCESSED;
                //$item['original_data'] = json_encode($item['original_data']);
                $items[] = $item;
            }
        }

        if (empty($items)) {
            infoLog('[createByBatch] items empty!');
            return $this->resObjectGet($result, 'crawl_result', $request->path());
        }
        $result = CrawlResult::insert($items);
        if (!$result) {
            errorLog('[createByBatch] insert fail.', $items);
            return response('插入失败', 402);
        }
        infoLog('[createByBatch] insert end.', $result);
        infoLog('[createByBatch] end.');
        return $this->resObjectGet($items, 'crawl_result', $request->path());
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
