<?php

namespace App\Http\Controllers\API\V1;

use Illuminate\Http\Request;
use Log;
use App\Services\ValidatorService;
use App\Services\InternalAPIService;

/**
 * QueueInfoController
 * 新版队列信息管理接口
 *
 * @author zhangwencheng@jinse.com
 * @version 1.1
 * Date: 2018/04/12
 */
class QueueInfoController extends Controller
{
    /**
     * getJob
     * 获取指定队列的任务
     *
     * @param item_id
     * @return array
     */
    public function getJob(Request $request)
    {
        $params = $request->all();
        ValidatorService::check($params, [
            'id' => 'required|integer|min:1|max:14',
        ]);

        $result = InternalAPIService::get('/queue_info/job', $params);
        return $this->resObjectGet($result, 'item', $request->path());
    }
}
