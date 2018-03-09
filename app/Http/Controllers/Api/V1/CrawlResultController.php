<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\CrawlResultCreateRequest;
use Illuminate\Http\Request;
use Log;
use Illuminate\Support\Facades\Validator;

class CrawlResultController extends Controller
{

    public function create(Request $request)
    {
        $result = [];
        $validator = Validator::make($request->all(), [
            'crawl_task_id' => 'numeric|nullable',
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
        //infoLog('[apiPushList] start');
        $validator = Validator::make($request->all(), [
            'data' => 'nullable',
            'crawl_task_id' => 'numeric|nullable',
            'task_start_time' => 'date|nullable',
            'task_end_time' => 'date|nullable',
            'task_url' => 'string|nullable',
            'setting_selectors' => 'string|nullable',
            'setting_keywords' => 'string|nullable',
            'setting_data_type' => 'numeric|nullable',
            'env' => 'numeric|nullable',
        ]);

        //infoLog('[apiPushList] $request->all()', $request->all());
        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                //infoLog('[pushList] $validator->fails()', $value);
                return response($value, 401);
            }
        }
        $params = [
            'data' => $request->data,
            'crawl_task_id' => $request->crawl_task_id,
            'task_start_time' => $request->task_start_time,
            'task_end_time' => $request->task_end_time,
            'task_url' => $request->task_url,
            'setting_selectors' => $request->setting_selectors,
            'setting_keywords' => $request->setting_keywords,
            'setting_data_type' => $request->setting_data_type,
            'env' => $request->env,
        ];

        $result = [];
        if (empty($params['data'])) {
            //infoLog('[apiPushList] $params["data"] empty!');
            return $this->resObjectGet($result, 'crawl_result', $request->path());
        }
        $dispatcher = app('Dingo\Api\Dispatcher');
        //infoLog('[apiPushList] internal_api/crawl/result/push_list');
        $resultData = $dispatcher->post('internal_api/crawl/result/push_list', $params = []);
        if ($resultData['status_code'] == 401) {
            //infoLog('[apiPushList] internal_api/crawl/result/push_list msg', $resultData['status_code']);
            return response('参数错误', 401);
        }
        //infoLog('[apiPushList] internal_api/crawl/result/push_list', $resultData);
        if ($resultData['data']) {
            $result = $resultData['data'];
        }
        //infoLog('[apiPushList] end');
        return $this->resObjectGet($result, 'crawl_result', $request->path());
    }

}
