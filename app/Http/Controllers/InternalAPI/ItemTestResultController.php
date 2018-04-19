<?php

namespace App\Http\Controllers\InternalAPI;

use App\Models\Item;
use Illuminate\Http\Request;
use Log;
use App\Services\ValidatorService;
use App\Models\ItemTestResult;
use App\Services\InternalAPIService;
use Dingo\Api\Exception\ResourceException;

/**
 * ItemTestResultController
 * 新版任务测试结果管理接口
 *
 * @author zhangwencheng@jinse.com
 * @version 1.1
 * Date: 2018/04/12
 */
class ItemTestResultController extends Controller
{

    /**
     * getTestResult
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
     * update
     * 更新
     *
     * @param Request $request
     * @return array
    */
    public function update(Request $request)
    {
        $params = $request->all();
        ValidatorService::check($params, [
            'id' => 'integer|required',
            'item_id' => 'integer|nullable',
            'item_run_log_id' => 'integer|nullable',
            'short_contents' => 'array|nullable',
            'long_contents' => 'array|nullable',
            'images' => 'array|nullable',
            'error_message' => 'string|nullable',
            'start_at' => 'date|nullable',
            'end_at' => 'date|nullable',
            'is_proxy' => 'required|integer',
        ]);

        $formatData = $this->__formatData($params);
        $itemTestResult = ItemTestResult::find($params['id']);

        if (empty($itemTestResult)) {
            throw new \Dingo\Api\Exception\ResourceException(" test result not exist");
        }

        $itemTestResult->update($formatData);
        $result = $itemTestResult->toArray();

        return $this->resObjectGet($result, 'item_test_result', $request->path());
    }

    /**
     * updateHtml
     * 更新html类型任务结果数据
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateHtml(Request $request)
    {
        $params = $request->all();
        ValidatorService::check($params, [
            'item_run_log_id' => 'integer|nullable',
            'short_contents' => 'array|nullable',
            'long_contents' => 'array|nullable',
            'images' => 'array|nullable',
            'error_message' => 'string|nullable',
            'start_at' => 'date|nullable',
            'end_at' => 'date|nullable',
            'is_proxy' => 'required|integer',
        ]);

        //获取测试结果
        $itemTestResult = InternalAPIService::get('/item/test_result', ['item_run_log_id' => $params['item_run_log_id']]);

        if (empty($itemTestResult)) {
            throw new ResourceException(" test result not exist");
        }

        //判断错误信息
        if (!empty($params['error_message'])) {
            //判断当前状态
            if ($itemTestResult['status'] == ItemTestResult::STATUS_NO_PROXY_TEST_FAIL || $itemTestResult['status'] == ItemTestResult::STATUS_PROXY_TEST_FAIL) {
                $params['status'] = ItemTestResult::STATUS_FAIL;
            } else {
                if ($params['is_proxy'] == Item::IS_PROXY_YES) {
                    $params['status'] = ItemTestResult::STATUS_PROXY_TEST_FAIL;
                } else {
                    $params['status'] = ItemTestResult::STATUS_NO_PROXY_TEST_FAIL;
                }
            }
        } else {
            $params['status'] = ItemTestResult::STATUS_SUCCESS;
            if (empty($params['short_contents']) && empty($params['long_contents'])) {
                $params['status'] = ItemTestResult::STATUS_FAIL;
            }
        }

        $formatData = $this->__formatData($params);

        if (empty($formatData['error_message'])) {
            $formatData['error_message'] = '';
        }

        ItemTestResult::find($itemTestResult['id'])->update($formatData);

        $result = [];
        if (empty($params['error_message'])) {// 判断如果没有错误信息则返回short_contents数组，否则返回空数组
            if (!empty($params['short_contents']) || !empty($params['long_contents'])) {
                $shortContent = json_decode($params['short_contents'][0], true);
                $shortContent['id'] = $itemTestResult['id'];
                $result[] = $shortContent;
            }
        }

        return $this->resObjectGet($result, 'item_test_result', $request->path());
    }

    /**
     * updateImage
     * 更新任务结果image信息
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateImage(Request $request)
    {
        $params = $request->all();
        ValidatorService::check($params, [
            'id' => 'integer|required',
            'images' => 'array|required',
        ]);

        $params['id'] = intval($params['id']);

        $itemTestResult = ItemTestResult::find($params['id']);
        if (empty($itemTestResult)) {
            throw new ResourceException("test result not exist");
        }

        $shortContents = json_decode($itemTestResult->short_contents, true);

        if (empty($shortContents)) {
            throw new ResourceException("test result short_contents is empty");
        }

        $item = json_decode($shortContents[0], true);
        $item['images'] = $params['images'];

        $shortContents[0] = json_encode($item);
        $itemTestResult->short_contents = $shortContents;
        $itemTestResult->save();

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
            'id' => '',
            'item_id' => '',
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
