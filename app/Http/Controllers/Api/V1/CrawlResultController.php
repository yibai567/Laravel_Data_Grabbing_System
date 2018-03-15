<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\CrawlResultCreateRequest;
use App\Services\APIService;
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
        infoLog("[createByBatch] start.");
        $validator = Validator::make($params, [
            'data' => 'nullable',
        ]);
        infoLog('[createByBatch] params validator start');
        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                errorLog('[createByBatch] params validator fail', $value);
                return response($value, 401);
            }
        }
        infoLog('[params validator] end.');

        $result = [];
        if (empty($params['data'])) {
            infoLog('[createByBatch] data empty!');
            return $this->resObjectGet($result, 'crawl_result', $request->path());
        }

        infoLog('[createByBatch] request internalApi createByBatch start');
        $data = APIService::internalPost('/internal/crawl/result/batch_result', $params);
        if ($data['status_code'] != 200) {
            errorLog('[createByBatch] request internalApi createByBatch result error', $data);
            return response($data['message'], $data['status_code']);
        }
        if ($data['data']) {
            $result = $data['data'];
        }
        infoLog('[createByBatch] request internalApi createByBatch end');
        infoLog('[createByBatch] end.');
        return $this->resObjectGet($result, 'crawl_result', $request->path());
    }

}
