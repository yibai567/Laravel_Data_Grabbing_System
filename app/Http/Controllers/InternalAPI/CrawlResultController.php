<?php

namespace App\Http\Controllers\InternalAPI;

use App\Http\Requests\CrawlResultCreateRequest;
use App\Http\Controllers\InternalAPI\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class CrawlResultController extends Controller
{
     // 1，默认，2测试
    const ENV_DEFAULT = 1;
    const ENV_TEST = 2;

    // public function create(Request $request)
    // {
    //     infoLog('[internalIpi/create] start');
    //     $params = $request->all();
    //     $validator = Validator::make($params, [
    //         'data' => 'nullable',
    //         'crawl_task_id' => 'string|nullable',
    //         'task_start_time' => 'date|nullable',
    //         'task_end_time' => 'date|nullable',
    //         'task_url' => 'string|nullable',
    //         'setting_selectors' => 'string|nullable',
    //         'setting_keywords' => 'string|nullable',
    //         'setting_data_type' => 'string|nullable',
    //     ]);

    //     if ($validator->fails()) {
    //         $errors = $validator->errors();
    //         foreach ($errors->all() as $value) {
    //             infoLog('[internalIpi/create] validator params', json_encode($value);
    //             return response($value, 401);
    //         }
    //     }

    //     $result = [];
    //     $newData = [];
    //     foreach ($params['data'] as $key => $value) {
    //         $matchingResult  = strpos($value['text'] , $params['setting_keywords']);
    //         if ($matchingResult !== false){
    //             $newData[] = $value;
    //         }
    //     }


    //     $dispatcher = app('Dingo\Api\Dispatcher');
    //     $data = $dispatcher->post('internal_api/basic/crawl/result', $params);
    //     if ($data['status_code'] == 401) {
    //         return response('参数错误', 401);
    //     }

    //     if ($data['data']) {
    //         $result = $data['data'];
    //     }
    //     return $this->resObjectGet($result, 'crawl_result', $request->path());
    // }

    public function pushList(Request $request)
    {
        infoLog('[internalIpi/pushList] start');
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
                infoLog('[internalIpi/pushList] validator params', json_encode($value);
                return response($value, 401);
            }
        }

        $result = [];
        if (empty($params['data'])) {
            infoLog('[internalIpi/pushList] $params["data"] empty!');
            return $this->resObjectGet($result, 'crawl_result', $request->path());
        }

        $params['original_data'] = $params['data'];
        $newData = [];
        foreach ($params['data'] as $key => $value) {
            $matchingResult  = strpos($value['text'] , $params['setting_keywords']);
            if ($matchingResult !== false){
                $newData[] = $value;
            }
        }

        if (empty($newData)) {
            infoLog('[internalIpi/pushList] $newData empty!');
            return $this->resObjectGet($result, 'crawl_result', $request->path());
        }

        $params['format_data'] = json_encode($newData);
        if ($env == self::ENV_TEST) {
            infoLog('[internalIpi/pushList] 如果env是测试类型则直接返回');
            return $this->resObjectGet($params, 'crawl_result', $request->path());
        }
        $dispatcher = app('Dingo\Api\Dispatcher');

        infoLog('[internalIpi/pushList] internal_api/pushList 接口调用 start');
        $resultData = $dispatcher->post('internal_api/basic/crawl/result/push_by_list', $params);
        if ($resultData['status_code'] == 401) {
            infoLog('[internalIpi/pushList] internal_api/crawl/result/push_list msg', json_encode($resultData));
            return response('参数错误', 401);
        }
        if ($resultData['data']) {
            $result = $resultData['data'];
        }
        infoLog('[internalIpi/pushList] internal_api/pushList 接口调用 end', json_encode($result));
        return $this->resObjectGet($result, 'crawl_result', $request->path());
    }
}
