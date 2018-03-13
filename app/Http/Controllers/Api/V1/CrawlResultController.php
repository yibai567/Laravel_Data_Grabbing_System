<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\CrawlResultCreateRequest;
use Illuminate\Http\Request;
use Log;
use App\Models\CrawlResult;
use Illuminate\Support\Facades\Validator;

class CrawlResultController extends Controller
{
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
        $params['effect'] = CrawlResult::EFFECT_DEFAULT;
        $resultData = $dispatcher->json($params)->post(config('api.api_base_url') . '/internal/crawl/result/batch_result');
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
