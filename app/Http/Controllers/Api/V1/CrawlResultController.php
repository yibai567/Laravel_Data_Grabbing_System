<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\APIService;
use Illuminate\Http\Request;
use Log;
use Illuminate\Support\Facades\Validator;

class CrawlResultController extends Controller
{
    /**
     * 支持单条，批量插入数据
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
    */
    public function createForBatch(Request $request)
    {
        $params = $request->all();
        infoLog("[v1:createForBatch] start.");
        $validator = Validator::make($params, [
            'task_id' => 'required|integer',
            'is_test' => 'integer|nullable',
            'start_time' => 'date|nullable',
            'end_time' => 'date|nullable',
            'result' => 'nullable',
        ]);
        infoLog('[v1:createForBatch] params validator start');
        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                errorLog('[v1:createForBatch] params validator fail', $value);
                return $this->resError(401, $value);
            }
        }
        infoLog('[v1:createForBatch] params validator end.');

        infoLog('[v1:createForBatch] request internalApi createForBatch start');
        $data = APIService::internalPost('/internal/crawl/results', $params);
        if ($data['status_code'] != 200) {
            errorLog('[v1:createForBatch] request internalApi createForBatch result error', $data);
            return response($data['status_code'], $data['message']);
        }
        if ($data['data']) {
            $result = $data['data'];
        }
        infoLog('[v1:createForBatch] request internalApi createForBatch end');

        infoLog('[v1:createForBatch] end.');
        return $this->resObjectGet($result, 'crawl_result', $request->path());
    }

}
