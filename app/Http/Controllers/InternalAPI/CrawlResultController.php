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

    public function pushList(Request $request)
    {
        $result = $request->all();
        //infoLog('[pushList] start', json_encode($result));
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
        //infoLog('[pushList] $request->all()', $request->all());
        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                //infoLog('[pushList] $validator->fails()', $value);
                return response($value, 401);
            }
        }
        $data = $request->data;
        $env =  $request->env;
        $params = [
            'crawl_task_id' => $request->crawl_task_id,
            'task_start_time' => $request->task_start_time,
            'task_end_time' => $request->task_end_time,
            'task_url' => $request->task_url,
            'setting_selectors' => $request->setting_selectors,
            'setting_keywords' => $request->setting_keywords,
            'setting_data_type' => $request->setting_data_type,
        ];

        $result = [];
        if (empty($data)) {
            //infoLog('[pushList] $params["data"] empty!');
            return $this->resObjectGet($result, 'crawl_result', $request->path());
        }

        $params['original_data'] = json_encode($data);
        $newData = [];
        //  var_dump($data);
        //$data = json_decode(json_encode($data), true);
        // exit();
       // $data = json_encode($data);
        foreach ($data as $key => $value) {
            $pos  = strpos($value['text'] , $params['setting_keywords']);
            if ($pos !== false){
                $newData[] = $value;
            }
        }
        if (empty($newData)) {
            //infoLog('[pushList] $newData empty!');
            return $this->resObjectGet($result, 'crawl_result', $request->path());
        }

        $params['format_data'] = json_encode($newData);
        //infoLog("+++++",$params);
        if ($env == self::ENV_TEST) {
            //infoLog('[pushList] $env 2');
            return $this->resObjectGet($params, 'crawl_result', $request->path());
        }
        $dispatcher = app('Dingo\Api\Dispatcher');

        infoLog('internal_api params', json_encode($params));
        $resultData = $dispatcher->post('internal_api/basic/crawl/result/push_by_list', $params = []);
        if ($resultData['status_code'] == 401) {
            infoLog('[pushList] internal_api/crawl/result/push_list msg', $resultData['status_code']);
            return response('参数错误', 401);
        }
        if ($resultData['data']) {
            $result = $resultData['data'];
        }
        //infoLog('[pushList] end', $result);
        return $this->resObjectGet($result, 'crawl_result', $request->path());
    }
}
