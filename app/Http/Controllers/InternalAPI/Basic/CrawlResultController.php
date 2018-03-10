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
    // public function create(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'crawl_task_id' => 'string|nullable',
    //         'original_data' => 'string|nullable',
    //         'task_start_time' => 'date|nullable',
    //         'task_end_time' => 'date|nullable',
    //         'task_url' => 'string|nullable',
    //         'format_data' => 'string|nullable',
    //         'setting_selectors' => 'string|nullable',
    //         'setting_keywords' => 'string|nullable',
    //         'setting_data_type' => 'string|nullable',
    //     ]);

    //     if ($validator->fails()) {
    //         $errors = $validator->errors();
    //         foreach ($errors->all() as $value) {
    //             return response($value, 401);
    //         }
    //     }
    //     $data = [
    //         'crawl_task_id' => $request->crawl_task_id,
    //         'original_data' => $request->original_data,
    //         'task_start_time' => $request->task_start_time,
    //         'task_end_time' => $request->task_end_time,
    //         'task_url' => $request->task_url,
    //         'format_data' => $request->format_data,
    //         'setting_selectors' => $request->setting_selectors,
    //         'setting_keywords' => $request->setting_keywords,
    //         'setting_data_type' => $request->setting_data_type,
    //     ];
    //     if (empty($data)) {
    //         return $this->resObjectGet($data, 'crawl_result', $request->path());
    //     }
    //     if ($this->isTaskExist($data['crawl_task_id'], $data['task_url'])) {
    //         return $this->resObjectGet($data['crawl_task_id'], 'crawl_result', $request->path());
    //     }
    //     $result = CrawlResult::create($data);
    //     return $this->resObjectGet($result, 'crawl_result', $request->path());
    // }

    /**
     * 支持单条，批量插入数据
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function pushList(Request $request)
    {
        infoLog('抓取平台结果返回增加基础API开始', $request->all());
        $params = $request->all();
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

        if ($validator->fails()) {
            infoLog('抓取平台结果返回增加基础API参数验证失败 $validator->fails()', $validator->fails());
            $errors = $validator->errors();
            infoLog('抓取平台结果返回增加基础API参数验证失败 $validator->errors()', $validator->errors());
            foreach ($errors->all() as $value) {
                infoLog('抓取平台结果返回增加基础API参数验证失败 $value', $value);
                return response($value, 401);
            }
        }

        $result = [];

        if (empty($params['format_data'])) {
            infoLog('抓取平台结果返回增加基础API $params["format_data"] empty!');
            return $this->resObjectGet($result, 'crawl_result', $request->path());
        }

        $items = [];
        infoLog('抓取平台结果返回增加基础API 处理数据是否存在', $params['format_data']);
        foreach (json_decode($params['format_data'], true) as $item) {
            //if (!$this->isTaskExist($params['crawl_task_id'], $item[1])) {
            $items[] = [
                'crawl_task_id' => $params['crawl_task_id'],
                'original_data' => $params['original_data'],
                'task_start_time' => $params['task_start_time'],
                'task_end_time' => $params['task_end_time'],
                'task_url' => $item['url'],
                'format_data' => $item,
                'setting_selectors' => $params['setting_selectors'],
                'setting_keywords' => $params['setting_keywords'],
                'setting_data_type' => $params['setting_data_type'],
                'status' => CrawlResult::IS_UNTREATED,
            ];
           // }
        }

        if (empty($items)) {
            infoLog('抓取平台结果返回增加基础API $items empty!');
            return $this->resObjectGet($result, 'crawl_result', $request->path());
        }

        infoLog('抓取平台结果返回增加基础API insert start', $items);
        $result = CrawlResult::insert($items);
        if (!$result) {
            infoLog('抓取平台结果返回增加基础API insert fail', $items);
            return response('插入失败', 402);
        }
        infoLog('抓取平台结果返回增加基础API insert end', $result);
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
