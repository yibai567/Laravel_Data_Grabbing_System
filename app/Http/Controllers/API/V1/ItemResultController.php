<?php

namespace App\Http\Controllers\API\V1;

use Illuminate\Http\Request;
use Log;
use App\Services\ValidatorService;
use App\Services\InternalAPIService;

/**
 * ItemResultController
 * 新版任务管理接口
 *
 * @author zhangwencheng@jinse.com
 * @version 1.1
 * Date: 2018/04/12
 */
class ItemResultController extends Controller
{

    /**
     * allByLast
     * 获取任务最后执行结果列表
     *
     * @param item_id
     * @return array
     */
    public function allByLast(Request $request)
    {
        $params = $request->all();
        ValidatorService::check($params, [
        ]);


        $result = InternalAPIService::get('/item/results', $params);
        return $this->resObjectGet($result, 'item', $request->path());
    }

    /**
     * getLastTestResult
     * 获取最后一次测试结果
     *
     * @param item_id
     * @return array
     */
    public function getLastTestResult(Request $request)
    {
        $params = $request->all();
        ValidatorService::check($params, [
        ]);


        $result = InternalAPIService::get('/item/test/result', $params);
        return $this->resObjectGet($result, 'item_result', $request->path());
    }

    /**
     * dispatchJob
     * 结果分发
     *
     * @param item_id (抓取任务ID)
     * @param is_test (是否是测试数据) 1测试|2插入
     * @param start_time (开始时间)
     * @param end_time (结束时间)
     * @param result (抓取结果)
     * @return array
     */
    public function dispatchJob(Request $request)
    {
        $params = $request->all();
        ValidatorService::check($params, [
            'item_run_log_id' => 'required|integer|min:1|max:99999999',
            'start_time'      => 'date|nullable',
            'end_time'        => 'date|nullable',
            'short_content'   => 'array|nullable',
            'long_content'    => 'array|nullable',
            'images'          => 'array|nullable',
        ]);

        // 获取 item run Log
        $itemRunLog = InternalAPIService::get('/item/run/log', ['id' => $params['item_run_log_id']]);
        if ($itemRunLog['status'] == 1) {

        }

        // 获取 item
        $item = InternalAPIService::get('/item', ['id' => $itemRunLog['item_id']]);

        // 判断 任务类型（test or production）、数据类型


        return $this->resObjectGet($result, 'item_result', $request->path());
    }
}























