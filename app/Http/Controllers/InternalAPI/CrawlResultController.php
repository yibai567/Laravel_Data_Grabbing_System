<?php

namespace App\Http\Controllers\InternalAPI;

use App\Http\Requests\CrawlResultCreateRequest;
use App\Http\Controllers\InternalAPI\Controller;
use Illuminate\Http\Request;
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
        $pos  = strpos($params['original_data'] , $params['setting_keywords']);
        if ( $pos  ===  false ) {
            return $result;
        }

        $dispatcher = app('Dingo\Api\Dispatcher');
        $data = $dispatcher->post('internal_api/basic/crawl/result', $params);
        if ($data['status_code'] == 401) {
            return response('参数错误', 401);
        }

        if ($data['data']) {
            $result = $data['data'];
        }
        return $this->resObjectGet($result, 'crawl_result', $request->path());
    }

    public function pushList()
    {

    }
}
