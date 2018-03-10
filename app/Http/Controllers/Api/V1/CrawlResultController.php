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
        infoLog('[apiCreate] start');
        $result = [];
        $params = $request->all();
        $validator = Validator::make($params, [
            'data' => 'nullable',
            'crawl_task_id' => 'numeric|nullable',
            'task_start_time' => 'date|nullable',
            'task_end_time' => 'date|nullable',
            'task_url' => 'string|nullable',
            'setting_selectors' => 'string|nullable',
            'setting_keywords' => 'string|nullable',
            'setting_data_type' => 'string|nullable',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                infoLog('[apiCreate] validator params', json_encode($value));
                return response($value, 401);
            }
        }

        if (empty($params['data'])) {
            infoLog('[apiCreate] $params["data"] empty!');
            return $this->resObjectGet($result, 'crawl_result', $request->path());
        }

        $dispatcher = app('Dingo\Api\Dispatcher');
        infoLog('[apiPushList] internal_api/create 接口调用 start');
        $resultData = $dispatcher->post('internal_api/crawl/result', $params);
        if ($resultData['status_code'] == 401) {
            infoLog('[apiPushList] internal_api/create 返回错误', json_encode($resultData));
            return response('参数错误', 401);
        }

        if ($resultData['data']) {
            $result = $resultData['data'];
        }
        return $this->resObjectGet($result, 'crawl_result', $request->path());
    }

    public function pushList(Request $request)
    {
        infoLog('[apiPushList] start');
        $params = $request->all();
        $validator = Validator::make($params, [
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

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                infoLog('[apiPushList] validator params', json_encode($value));
                return response($value, 401);
            }
        }
        $result = [];
        if (empty($params['data'])) {
            infoLog('[apiPushList] $params["data"] empty!');
            return $this->resObjectGet($result, 'crawl_result', $request->path());
        }
        $dispatcher = app('Dingo\Api\Dispatcher');
        infoLog('[apiPushList] internal_api 接口调用 start');
        $resultData = $dispatcher->post('internal_api/crawl/result/push_list', $params = []);

        if ($resultData['status_code'] == 401) {
            infoLog('[apiPushList] internal_api 返回错误', json_encode($resultData));
            return response('参数错误', 401);
        }

        if ($resultData['data']) {
            $result = $resultData['data'];
        }
        infoLog('[apiPushList] end');
        return $this->resObjectGet($result, 'crawl_result', $request->path());
    }

}
