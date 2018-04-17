<?php

namespace App\Http\Controllers\InternalAPI;

use Illuminate\Http\Request;
use Log;
use App\Services\ValidatorService;
use App\Models\ItemResult;

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
     * all
     * 获取任务结果列表
     *
     * @param item_id
     * @return array
     */
    public function all(Request $request)
    {
        $params = $request->all();
        ValidatorService::check($params, [
        ]);


        $result = InternalAPIService::get('/item/results', $params);
        return $this->resObjectGet($result, 'item', $request->path());
    }

    /**
     * createAllToJson
     * 批量插入数据, 对于接口请求的结果
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
    */
    public function createAllToJson(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'task_id' => 'required|integer',
            'is_test' => 'integer|nullable',
            'start_time' => 'date|nullable',
            'end_time' => 'date|nullable',
            'result' => 'nullable',
        ]);


        return $this->resObjectList($saveResult, 'item_result', $request->path());
    }

    /**
     * createAllToHtml
     * 批量插入数据, 对于页面请求的结果
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
    */
    public function createAllToHtml(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'task_id' => 'required|integer',
            'is_test' => 'integer|nullable',
            'start_time' => 'date|nullable',
            'end_time' => 'date|nullable',
            'result' => 'nullable',
        ]);



        return $this->resObjectList($saveResult, 'item_result', $request->path());
    }

    /**
     * createAllToImage
     * 批量插入数据, 对于页面请求的结果
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
    */
    public function createAllToImage(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'task_id' => 'required|integer',
            'is_test' => 'integer|nullable',
            'start_time' => 'date|nullable',
            'end_time' => 'date|nullable',
            'result' => 'nullable',
        ]);



        return $this->resObjectList($saveResult, 'item_result', $request->path());
    }

    /**
     * all
     * 获取任务最后执行结果列表
     *
     * @param item_id
     * @return array
     */
    public function allByLast(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'item_id' => 'required|integer'
        ]);

        $type = ItemRunLog::TYPE_PRO;

        $itemRunLog = InternalAPIService::get('/item_run_log/item/' . $params['item_id'] . '?type=' . $type);

        if (empty($itemRunLog)) {
            throw new \Dingo\Api\Exception\ResourceException("item_run_log_id not exist");
        }

        $res = ItemTestResult::where('item_run_log_id', $itemRunLog['id'])
                            ->get();
        $resData = [];

        if (!empty($res)) {
            $resData = $res->toArray();
        }

        return $this->resObjectGet($resData, 'item_result', $request->path());
    }


    /**
     * update
     * 更新
     *
     * @param Request $request
     * return array
     */
    public function update(Request $request, $id)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'id' => 'required|integer',
            'item_id' => 'nullable|integer',
            'item_run_log_id' => 'nullable|integer',
            'short_contents' => 'nullable|text',
            'md5_short_contents' => 'nullable|text',
            'long_content0' => 'nullable|text',
            'long_content1' => 'nullable|text',
            'images' => 'nullable|text',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date',
            'status' => 'nullable|integer',
        ]);

        $itemResult = ItemResult::find($params['id']);
        if (empty($itemResult)) {
            return $this->resError(405, 'item result does not exist！');
        }

        $itemResult->update($params);
        $result = $itemResult->toArray();
        return $this->resObjectGet($result, 'item_result', $request->path());
    }
}
