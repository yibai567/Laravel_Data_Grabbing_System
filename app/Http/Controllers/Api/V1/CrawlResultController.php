<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\CrawlResultCreateRequest;
use Illuminate\Http\Request;
use Log;
use Illuminate\Support\Facades\Validator;

class CrawlResultController extends Controller
{
    const EFFECT_DEFAULT = 1;
    const EFFECT_TEST = 2;
    // public function create(Request $request)
    // {
    //infoLog('[apiCreate] start');
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
    //infoLog('[apiCreate] validator params', json_encode($value));
    //             return response($value, 401);
    //         }
    //     }

    //     if (empty($params['data'])) {
    //infoLog('[apiCreate] $params["data"] empty!');
    //         return $this->resObjectGet($result, 'crawl_result', $request->path());
    //     }

    //     $dispatcher = app('Dingo\Api\Dispatcher');
    //infoLog('[apiPushList] internal_api/create 接口调用 start');
    //     $resultData = $dispatcher->post('internal_api/crawl/result', $params);
    //     if ($resultData['status_code'] == 401) {
    //infoLog('[apiPushList] internal_api/create 返回错误', json_encode($resultData));
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
    public function createByBatch(Request $request)
    {
        $params = $request->all();
        infoLog("[createByBatch] start.", $params);
        $validator = Validator::make($params, [
            'data' => 'nullable',
            'crawl_task_id' => 'numeric|nullable',
            'task_start_time' => 'date|nullable',
            'task_end_time' => 'date|nullable',
            'task_url' => 'string|nullable',
            'setting_selectors' => 'string|nullable',
            'setting_keywords' => 'string|nullable',
            'setting_data_type' => 'numeric|nullable',
            'effect' => 'numeric|nullable',
        ]);
        infoLog('[createByBatch] params validator start', $params);
        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                errorLog('[createByBatch] params validator fail', $value);
                return response($value, 401);
            }
        }
        $result = [];
        infoLog('[params validator] end.');
        if (empty($params['data'])) {
            infoLog('[createByBatch] data empty!');
            return $this->resObjectGet($result, 'crawl_result', $request->path());
        }
        infoLog('[createByBatch] request internalApi createByBatch start', $params);
        $dispatcher = app('Dingo\Api\Dispatcher');
        $params['effect'] = self::EFFECT_DEFAULT;
        $resultData = $dispatcher->json($params)->post('internal/crawl/result/batch_result');
        if ($resultData['status_code'] != 200) {
            errorLog('[createByBatch] request internalApi createByBatch result error', $resultData);
            return response($resultData['message'], $resultData['status_code']);
        }
        if ($resultData['data']) {
            $result = $resultData['data'];
        }
        infoLog('[createByBatch] request internalApi createByBatch end');
        infoLog('[createByBatch] end.', $result);
        return $this->resObjectGet($result, 'crawl_result', $request->path());
    }

}
