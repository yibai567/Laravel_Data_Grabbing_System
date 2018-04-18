<?php

namespace App\Http\Controllers\InternalAPI;

use Illuminate\Http\Request;
use Log;
use App\Services\ValidatorService;
use App\Models\ItemTestResult;
use App\Models\ItemRunLog;
use App\Services\InternalAPIService;

/**
 * ItemTestResultController
 * 新版任务管理接口
 *
 * @author zhangwencheng@jinse.com
 * @version 1.1
 * Date: 2018/04/12
 */
class ItemTestResultController extends Controller
{

    /**
     * getByLast
     * 获取任务最后测试结果
     *
     * @param item_id
     * @return array
     */
    public function getTestResult(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'item_run_log_id' => 'required|integer'
        ]);

        $res = ItemTestResult::where('item_run_log_id', $params['item_run_log_id'])
                            ->first();
        $resData = [];

        if (!empty($res)) {
            $resData = $res->toArray();
        }

        return $this->resObjectGet($resData, 'item_result', $request->path());

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

        $itemParams = $params;

        ValidatorService::check($itemParams, [
            'item_run_log_id' => 'required|integer|min:1|max:99999999',
            'start_time'      => 'date|nullable',
            'end_time'        => 'date|nullable',
            'short_content'   => 'nullable',
            'long_content'    => 'array|nullable',
            'images'          => 'array|nullable',
            'error_message'   => 'string|nullable',
        ]);

        $itemRunLog = InternalAPIService::get('/');

        dd($item);



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
     * update
     * 更新
     *
     * @param Request $request
     * @return array
    */
    public function update(Request $request)
    {
        $params = $request->all();

        $testResult = $params;
        ValidatorService::check($testResult, [
            'item_id' => 'required|nullable',
            'item_run_log_id' => 'integer|nullable',
            'short_content' => 'array|nullable',
            'long_content' => 'array|nullable',
            'images' => 'array|nullable',
            'error_message' => 'string|nullable',
            'start_at' => 'date|nullable',
            'end_at' => 'date|nullable',
            'is_proxy' => 'required|integer',
        ]);

        //获取测试结果
        $itemRunLog = InternalAPIService::get('/item/test_result', ['item_run_log_id' => $testResult['item_run_log_id']]);

        if (empty($itemRunLog)) {
            throw new \Dingo\Api\Exception\ResourceException(" test result not exist");
        }
        //判断错误信息
        if (!empty($testResult['error_message'])) {
            //判断当前状态
            if ($itemRunLog['status'] == ItemTestResult::STATUS_INIT) {
                if ($testResult['is_proxy'] == Item::IS_PROXY_YES) {
                    $testResult['status'] = ItemTestResult::STATUS_NO_PROXY_TEST;
                } else {
                    $testResult['status'] = ItemTestResult::STATUS_PROXY_TEST;
                }
            } else if ($itemRunLog['status'] == ItemTestResult::STATUS_NO_PROXY_TEST) {
                $testResult['status'] = ItemTestResult::STATUS_FAIL;
            } else if ($itemRunLog['status'] == ItemTestResult::STATUS_PROXY_TEST) {
                $testResult['status'] = ItemTestResult::STATUS_FAIL;
            }
        } else {
            if (empty($testResult['short_content']) && empty($testResult['long_content'])) {
                return $this->resObjectGet([], 'item_test_result', $request->path());
            }
            $testResult['status'] = ItemTestResult::STATUS_SUCCESS;
        }

        $formatData = $this->__formatData($testResult);

        $itemTestResult = ItemTestResult::find($itemRunLog['id']);

        $itemTestResult->update($formatData);

        $result = $itemTestResult->toArray();

        return $this->resObjectGet($result, 'item_test_result', $request->path());
    }

    /**
     * create
     * 插入数据
     *
     * @param Request $request
     * @return array
    */
    public function create(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'item_id' => 'required|integer',
            'item_run_log_id' => 'integer|nullable',
            'short_contents' => 'array|nullable',
            'long_content' => 'array|nullable',
            'images' => 'array|nullable',
            'error_message' => 'string|nullable',
            'start_at' => 'date|nullable',
            'end_at' => 'date|nullable',
        ]);

        $params['status'] = ItemTestResult::STATUS_INIT;

        $itemTestResult = ItemTestResult::create($params);

        $result = [];

        if (!empty($itemTestResult)) {
            $result = $itemTestResult->toArray();
        }

        return $this->resObjectGet($result, 'item_result', $request->path());
    }

    private function __formatData($params)
    {
        $item = [
            'short_contents'  => '',
            'md5_short_contents' => '',
            'long_content0' => '',
            'long_content1' => '',
            'images' => '',
            'error_message' => '',
            'start_at' => '',
            'end_at' => '',
            'status' => ''
        ];

        $data = [];

        foreach ($item as $key => $value) {
            if (!empty($params[$key])) {
                $data[$key] = $params[$key];
            }
        }

        if (!empty($data['short_contents'])) {
            $data['short_contents'] = json_encode($data['short_contents']);
            $data['md5_short_contents'] = md5(json_encode($data['short_contents']));
        }
        return $data;
    }

}























