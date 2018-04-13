<?php

namespace App\Http\Controllers\InternalAPI;

use Illuminate\Http\Request;
use Log;
use App\Services\ValidatorService;
use App\Services\ItemService;
use App\Services\InternalAPIService;
use App\Models\Item;


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

        $itemService = new ItemService();

        //获取参数验证规则
        $paramsVerifyRule = $itemService->paramsVerifyRule();

        //参数验证
        ValidatorService::check($resData, $paramsVerifyRule);
        //参数格式化
        $formatParams = $itemService->paramsFormat($resData);

        $formatParams['status'] = Item::STATUS_INIT;

        $res = Item::create($formatParams);

        $result = [];
        if (!empty($res)) {
            $result = $res->toArray();
        }
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
        ValidatorService::check($params, [
        ]);


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

        $res = Item::where('id', $params['id'])->get();

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

        ValidatorService::check($params, [
            'id' => 'integer|required',
        ]);

        $itemDetail = InternalAPIService::get('/item', $params);
        dd($itemDetail);

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
        ValidatorService::check($params, [
            'id' => 'integer|required',
        ]);

        $result = InternalAPIService::post('/item/stop', $params);

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
        ValidatorService::check($params, [
            'id' => 'integer|required',
        ]);

        $result = InternalAPIService::post('/item/test', $params);

        return $this->resObjectGet('测试提交成功，请稍后查看结果！', 'item', $request->path());
    }
}























