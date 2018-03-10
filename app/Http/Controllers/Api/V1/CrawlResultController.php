<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\CrawlResultCreateRequest;
use Illuminate\Http\Request;
use Log;
use Illuminate\Support\Facades\Validator;

class CrawlResultController extends Controller
{

    // public function create(Request $request)
    // {
    //     infoLog('[apiCreate] start');
    //     $result = [];
    //     $params = $request->all();
    //     $validator = Validator::make($params, [
    //         'data' => 'nullable',
    //         'crawl_task_id' => 'numeric|nullable',
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
    //             infoLog('[apiCreate] validator params', json_encode($value));
    //             return response($value, 401);
    //         }
    //     }

    //     if (empty($params['data'])) {
    //         infoLog('[apiCreate] $params["data"] empty!');
    //         return $this->resObjectGet($result, 'crawl_result', $request->path());
    //     }

    //     $dispatcher = app('Dingo\Api\Dispatcher');
    //     infoLog('[apiPushList] internal_api/create 接口调用 start');
    //     $resultData = $dispatcher->post('internal_api/crawl/result', $params);
    //     if ($resultData['status_code'] == 401) {
    //         infoLog('[apiPushList] internal_api/create 返回错误', json_encode($resultData));
    //         return response('参数错误', 401);
    //     }

    //     if ($resultData['data']) {
    //         $result = $resultData['data'];
    //     }
    //     return $this->resObjectGet($result, 'crawl_result', $request->path());
    // }

    /**
     * 支持单条，批量插入数据
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
    */
    public function pushList(Request $request)
    {
        infoLog('抓取平台结果添加外部API开始', $request->all());
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
        infoLog('抓取平台结果添加外部API参数验证', $validator);
        if ($validator->fails()) {
            infoLog('抓取平台结果添加外部API参数验证失败 $validator->fails()', $validator->fails());
            $errors = $validator->errors();
            infoLog('抓取平台结果添加外部API参数验证失败 $errors', $errors);
            foreach ($errors->all() as $value) {
                infoLog('抓取平台结果添加外部API参数验证失败 $value', $value);
                return response($value, 401);
            }
        }
        $result = [];
        infoLog('抓取平台结果添加外部API $params["data"]参数验证', $params['data']);
        if (empty($params['data'])) {
            infoLog('抓取平台结果添加外部API $params["data"] empty!');
            return $this->resObjectGet($result, 'crawl_result', $request->path());
        }
        $dispatcher = app('Dingo\Api\Dispatcher');
        infoLog('抓取平台结果添加请求业务API开始');
        //测试参数
        $newParams['id'] = 1;
        $newParams['title'] = '测试测试';
        //end
        $resultData = $dispatcher->post('internal/crawl/result/push_list', $newParams);
        if ($resultData['status_code'] == 401) {
            infoLog('抓取平台结果添加请求业务API参数错误', $params);
            return response('参数错误', 401);
        }
        if ($resultData['data']) {
            infoLog('抓取平台结果添加请求业务API成功返回结果', $resultData);
            $result = $resultData['data'];
        }
        infoLog('抓取平台结果添加请求业务API结束');
        return $this->resObjectGet($result, 'crawl_result', $request->path());
    }

}
