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

    /**
     * 支持单条，批量插入数据
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
    */
    public function pushList(Request $request)
    {
        infoLog('抓取平台结果添加业务API开始', $request->all());
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
            infoLog('抓取平台结果添加业务API参数验证失败 $validator->fails()', $validator->fails());
            $errors = $validator->errors();
            infoLog('抓取平台结果添加业务API参数验证失败 $errors->all()', $errors->all());
            foreach ($errors->all() as $value) {
                infoLog('抓取平台结果添加业务API参数验证失败 $value', $value);
                return response($value, 401);
            }
        }

        $result = [];
        if (empty($params['data'])) {
            infoLog('抓取平台结果添加业务API $params["data"] empty!');
            return $this->resObjectGet($result, 'crawl_result', $request->path());
        }

        $params['original_data'] = $params['data'];
        $newData = [];
        infoLog('抓取平台结果添加业务API 匹配关键词');
        foreach ($params['data'] as $key => $value) {
            $matchingResult  = strpos($value['text'], $params['setting_keywords']);
            if ($matchingResult !== false){
                $newData[] = $value;
            }
        }

        if (empty($newData)) {
            infoLog('抓取平台结果添加业务API 匹配关键词结果为空');
            return $this->resObjectGet($result, 'crawl_result', $request->path());
        }

        $params['format_data'] = json_encode($newData);

        if ($env == self::ENV_TEST) {
            infoLog('抓取平台结果添加业务API env是测试类型', $env);
            return $this->resObjectGet($params, 'crawl_result', $request->path());
        }
        $dispatcher = app('Dingo\Api\Dispatcher');

        infoLog('抓取平台结果添加请求基础API开始');
        $resultData = $dispatcher->post('internal_api/basic/crawl/result/push_by_list', $params);
        if ($resultData['status_code'] == 401) {
            infoLog('抓取平台结果添加请求基础API参数错误', $params);
            return response('参数错误', 401);
        }
        if ($resultData['data']) {
            infoLog('抓取平台结果添加请求基础API成功返回结果', $resultData);
            $result = $resultData['data'];
        }
        infoLog('抓取平台结果添加请求基础API结束');
        return $this->resObjectGet($result, 'crawl_result', $request->path());
    }
}
