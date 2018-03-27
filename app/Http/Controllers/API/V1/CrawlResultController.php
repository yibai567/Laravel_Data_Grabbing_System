<?php

namespace App\Http\Controllers\API\V1;

use App\Services\APIService;
use Illuminate\Http\Request;
use Log;
use Illuminate\Support\Facades\Validator;
use App\Services\ValidatorService;

/**
 * CrawlResultController
 * 任务抓取结果控制器
 * @author huangxingxing@jinse.com
 * @version 1.1
 * Date: 2018/03/25
 */
class CrawlResultController extends Controller
{
    /**
     * createForBatch
     * 保存抓取结果
     *
     * @param task_id (抓取任务ID)
     * @param is_test (是否是测试数据) 1测试|2插入
     * @param start_time (开始时间)
     * @param end_time (结束时间)
     * @param result (抓取结果)
     * @return array
     */
    public function createForBatch(Request $request)
    {
        $params = $request->all();


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

        return $this->resObjectGet($data, 'crawl_result', $request->path());
    }
}
