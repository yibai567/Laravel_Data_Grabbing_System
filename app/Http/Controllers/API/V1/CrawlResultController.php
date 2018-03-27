<?php

namespace App\Http\Controllers\API\V1;

use App\Services\APIService;
use Illuminate\Http\Request;
use Log;
use Illuminate\Support\Facades\Validator;
use App\Services\ValidatorService;

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

        $check = ValidatorService::check($params, [
            'task_id' => 'required|integer',
            'is_test' => 'integer|nullable',
            'start_time' => 'date|nullable',
            'end_time' => 'date|nullable',
            'result' => 'nullable',
        ]);

        if ($check) {
            return $check;
        }

        $data = APIService::internalPost('/internal/crawl/results', $params);

        if ($data === false) {
            return $this->resError(501, '系统内部错误');
        }

        infoLog('[v1:createForBatch] end.');
        return $this->resObjectGet($data, 'crawl_result', $request->path());
    }
}
