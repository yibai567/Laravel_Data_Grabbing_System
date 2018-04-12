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

}























