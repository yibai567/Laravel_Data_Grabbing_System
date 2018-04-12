<?php

namespace App\Http\Controllers\InternalAPI;

use Illuminate\Http\Request;
use Log;
use App\Services\ValidatorService;

/**
 * QueueInfoController
 * 任务运行日志
 *
 * @author zhangwencheng@jinse.com
 * @version 1.1
 * Date: 2018/04/12
 */
class QueueInfoController extends Controller
{

    /**
     * updateCurrentLength
     * 更新
     *
     * @param
     * @return array
     */
    public function updateCurrentLength(Request $request)
    {
        $params = $request->all();
        ValidatorService::check($params, [
        ]);


        return $this->resObjectGet($result, 'queue_info', $request->path());
    }

    /**
     * getJobByName
     * 获取指定队列的任务
     *
     * @param item_id
     * @return array
     */
    public function getJobByName(Request $request)
    {
        $params = $request->all();
        ValidatorService::check($params, [
        ]);


        $result = InternalAPIService::get('/item/test/result', $params);
        return $this->resObjectGet($result, 'item', $request->path());
    }

    /**
     * createJobByName
     * 获取指定队列的任务
     *
     * @param item_id
     * @return array
     */
    public function createJobByName(Request $request)
    {
        $params = $request->all();
        ValidatorService::check($params, [
        ]);


        $result = InternalAPIService::get('/item/test/result', $params);
        return $this->resObjectGet($result, 'item', $request->path());
    }

}























