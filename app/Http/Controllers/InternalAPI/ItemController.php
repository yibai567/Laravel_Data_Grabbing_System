<?php

namespace App\Http\Controllers\InternalAPI;

use Illuminate\Http\Request;
use Log;
use App\Services\ValidatorService;
use App\Services\ItemService;
use App\Services\InternalAPIService;
use App\Models\Item;
use App\Models\QueueInfo;
use App\Models\ItemRunLog;

/**
 * ItemController
 * 新版任务管理接口
 *
 * @author zhangwencheng@jinse.com
 * @version 1.1
 * Date: 2018/04/12
 */
class ItemController extends Controller
{

    /**
     * create
     * 任务创建
     *
     * @param data_type (数据类型 1 html | 2 json)
     * @param content_type (内容类型 1 短内容 | 2 内容)
     * @param resource_url (资源URL)
     * @param is_capture_image (是否截取图片 1 true | 2 false)
     * @param short_content_selector (短内容选择器)
     * @param long_content_selector (长内容选择器)
     * @param row_selector (行内选择器)
     * @param cron_type (执行频次 1 持续执行, 2 每分钟执行一次, 3 每小时执行一次, 4 每天执行一次)
     * @param is_proxy (是否翻墙 1 翻墙 | 2 不翻墙)
     * @return array
     */
    public function create(Request $request)
    {
        $params = $request->all();

        $resData = $params;

        //获取参数验证规则
        $paramsVerifyRule = $this->itemService->verifyParamsRule();
        //参数验证
        ValidatorService::check($resData, $paramsVerifyRule);
        //参数格式化
        $formatParams = $this->itemService->formatParams($resData);

        $formatParams['status'] = Item::STATUS_INIT;

        $res = Item::create($formatParams);

        $result = [];

        if (!empty($res)) {
            $result = $res->toArray();
        }

        $this->__createQueue($result);

        return $this->resObjectGet($result, 'item', $request->path());
    }

    /**
     * update
     * 更新任务
     *
     * @param
     * @return array
     */
    public function update(Request $request)
    {
        $params = $request->all();

        $resData = $params;

        $paramsVerifyRule = $this->itemService->updateParamsVerifyRule();

        ValidatorService::check($resData, $paramsVerifyRule);

        //参数格式化
        $formatParams = $this->itemService->verifySelector($resData);

        $item = Item::find($formatParams['id']);
        $item->status = Item::STATUS_INIT;

        foreach ($formatParams as $key=>$value) {
            if (isset($value)) {
                $item->{$key} = $value;
            }
        }

        if ($item->save()) {
            $result = $item->toArray();
        }

        $this->__createQueue($result);

        return $this->resObjectGet($result, 'item', $request->path());
    }

    /**
     * retrieve
     * 获取任务详情
     *
     * @param
     * @return array
     */
    public function retrieve(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            "id" => "required|integer"
        ]);

        $res = Item::find($params['id']);
        $resData = [];

        if (!empty($res)) {
            $resData = $res->toArray();
        }

        return $this->resObjectGet($resData, 'item', $request->path());
    }

    /**
     * start
     * 任务开始
     *
     * @param id (任务ID)
     * @return array
     */
    public function start(Request $request)
    {
        $params = $request->all();
        $itemParams = $params;

        ValidatorService::check($params, [
            'id' => 'integer|required',
        ]);

        $itemDetail = $this->__getDetail($itemParams['id']);

        if ($itemDetail['status'] != Item::STATUS_TEST_SUCCESS && $itemDetail['status'] != Item::STATUS_STOP) {
            throw new \Dingo\Api\Exception\ResourceException("item status does not allow to start");
        }

        $itemParams['status'] = Item::STATUS_START;

        $result = InternalAPIService::post('/item/update', $itemParams);

        return $this->resObjectGet($result, 'item', $request->path());
    }

    /**
     * stop
     * 任务停止
     *
     * @param id (任务ID)
     * @return array
     */
    public function stop(Request $request)
    {
        $params = $request->all();

        $itemParams = $params;

        ValidatorService::check($params, [
            'id' => 'integer|required',
        ]);

        $itemDetail = $this->__getDetail($itemParams['id']);

        if ($itemDetail['status'] != Item::STATUS_START) {
            throw new \Dingo\Api\Exception\ResourceException("item status does not allow to stop");
        }

        $itemParams['status'] = Item::STATUS_STOP;

        $result = InternalAPIService::post('/item/update', $itemParams);

        return $this->resObjectGet($result, 'item', $request->path());

    }

    /**
     * test
     * 任务测试
     *
     * @param id (任务ID)
     * @return array
     */
    public function test(Request $request)
    {
        $params = $request->all();

        $formatParams['item_id'] = $params['id'];

        ValidatorService::check($params, [
            'id' => 'integer|required',
        ]);

        $params['status'] = Item::STATUS_TESTING;

        InternalAPIService::post('/item/update', $params);

        $itemDetail = $this->__getDetail($formatParams['item_id']);

        $this->__createQueue($itemDetail);

        $type = ItemRunLog::TYPE_TEST;

        $itemRunLog = InternalAPIService::get('/item_run_log/item/', ['item_id' => $formatParams['item_id'], 'type' => $type ]);
        if (empty($itemRunLog)) {
            throw new \Dingo\Api\Exception\ResourceException("item_run_log_id not exist");
        }

        $itemTestResult = InternalAPIService::post('/item/test/result/', ['item_id' => $formatParams['item_id'], 'item_run_log_id' => $itemRunLog['id']]);

        if (empty($itemTestResult)) {
            throw new \Dingo\Api\Exception\ResourceException(" create item_test_result fail");
        }


        return $this->resObjectGet('测试提交成功，请稍后查看结果！', 'item', $request->path());
    }

    /**
     * __createQueue
     * 创建队列
     *
     * @param $result
     */
    private function __createQueue($result)
    {
        //入队列
        $formatParams['id'] = config('crawl_queue.' . QueueInfo::TYPE_TEST . '.' . $result['data_type'] . '.' . $result['is_proxy']);

        $formatParams['item_id'] = $result['id'];

        InternalAPIService::post('/queue_info/job', $formatParams);
    }

    /**
     * __getDetail
     * 获取任务详情
     *
     * @param id
     * @return array
     */
    private function __getDetail($id)
    {
        $itemDetail = InternalAPIService::get('/item', ['id' => $id]);

        if (empty($itemDetail)) {
            throw new \Dingo\Api\Exception\ResourceException("item does not exist");
        }

        return $itemDetail;
    }

}























