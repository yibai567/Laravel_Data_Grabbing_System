<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\CrawlResultCreateRequest;
use Illuminate\Support\Facades\Validator;

class CrawlResultController extends Controller
{
    public function create(Request $request)
    {
        $result = [];
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
        $params = [
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

        $dispatcher = app('Dingo\Api\Dispatcher');
        $data = $dispatcher->post('internal_api/crawl/result', $params);
        if ($data['status_code'] == 401) {
            return response('参数错误', 401);
        }

        if ($data['data']) {
            $result = $data['data'];
        }
        return $this->resObjectGet($result, 'crawl_result', $request->path());
    }

    public function pushList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'data' => 'string',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                return response($value, 401);
            }
        }
        $params = [
            'data' => $request->data
        ];

        $dispatcher = app('Dingo\Api\Dispatcher');
        $resultData = $dispatcher->post('internal_api/crawl/result/push_by_list', $params);
        if ($resultData['status_code'] == 401) {
            return response('参数错误', 401);
        }
        if ($resultData['data']) {
            $result = $resultData['data'];
        }
        return $this->resObjectGet($result, 'crawl_result', $request->path());
    }
}
