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
        //infoLog('[pushByList] start');
        $newData = $request->all();
        return infoLog('[pushByList] $params', json_encode($newData));
        $validator = Validator::make($request->all(), [
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
        //infoLog('[pushByList] $request->all()', $request->all());
        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                //infoLog('[pushByList] $validator->fails()', $value);
                return response($value, 401);
            }
        }
        $params = [
            'crawl_task_id' => $request->crawl_task_id,
            'task_start_time' => $request->task_start_time,
            'task_end_time' => $request->task_end_time,
            'task_url' => $request->task_url,
            'setting_selectors' => $request->setting_selectors,
            'setting_keywords' => $request->setting_keywords,
            'setting_data_type' => $request->setting_data_type,
            'original_data' => $request->original_data,
            'format_data' => $request->format_data,
        ];
        $result = [];
        infoLog('[pushByList] $params["format_data"] == ' . json_encode($params));
        if (empty($params['format_data'])) {
            infoLog('[pushByList] $params["format_data"] empty!');
            return $this->resObjectGet($result, 'crawl_result', $request->path());
        }
        $items = [];
        infoLog('[pushByList] insert');
        try{
            foreach (json_decode($params['format_data'], true) as $item) {
            //if (!$this->isTaskExist($params['crawl_task_id'], $item[1])) {
                $items[] = [
                    'crawl_task_id' => $params['crawl_task_id'],
                    'original_data' => $params['original_data'],
                    'task_start_time' => $params['task_start_time'],
                    'task_end_time' => $params['task_end_time'],
                    'task_url' => $item['url'],
                    'format_data' => json_encode($item),
                    'setting_selectors' => $params['setting_selectors'],
                    'setting_keywords' => $params['setting_keywords'],
                    'setting_data_type' => $params['setting_data_type'],
                    'status' => CrawlResult::IS_UNTREATED,
                ];
           // }

            }
        } catch(Exception $e) {
            infoLog('error',$e);
        }



        DB::beginTransaction();

        $result = CrawlResult::insert($items);

        DB::commit();
        if (!$result) {
            DB::rollBack();
            //infoLog('[pushByList] insert fail');
            return response('更新失败', 402);
        }
        //infoLog('[pushByList] end');
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
