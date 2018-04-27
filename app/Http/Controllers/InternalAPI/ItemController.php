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
        Log::debug('[create] 创建任务信息', $params);

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
        $formatParams['status'] = Item::STATUS_INIT;
        foreach ($formatParams as $key=>$value) {
            if (isset($value)) {
                $item->{$key} = $value;
            }
        }
        if ($item->save()) {
            $result = $item->toArray();
        }

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

        ValidatorService::check($params, [
            'id' => 'integer|required',
        ]);

        $item = Item::find($params['id']);

        if (empty($item)) {
            Log::debug('[start] item not exist', $params);
            throw new \Dingo\Api\Exception\ResourceException(" item not exist");
        }
        $itemDetail = $item->toArray();

        if ($itemDetail['status'] != Item::STATUS_TEST_SUCCESS && $itemDetail['status'] != Item::STATUS_STOP) {
            Log::debug('[start] item status does not allow to start', $itemDetail);
            throw new \Dingo\Api\Exception\ResourceException("item status does not allow to start");
        }

        $item->status = Item::STATUS_START;
        $item->save();
        $res = $item->toArray();

        if (empty($res)) {
            Log::debug('[start] update status fail');
            throw new \Dingo\Api\Exception\ResourceException("update status fail");
        }
        return $this->resObjectGet($res, 'item', $request->path());
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

        $item = Item::find($params['id']);


        if (empty($item)) {
            Log::debug('[stop] item not exist', $params);
            throw new \Dingo\Api\Exception\ResourceException(" item not exist");
        }
        $itemDetail = $item->toArray();

        if ($itemDetail['status'] != Item::STATUS_START) {
            Log::debug('[stop] item status does not allow to stop', $itemDetail);
            throw new \Dingo\Api\Exception\ResourceException("item status does not allow to stop");
        }

        $item->status = Item::STATUS_STOP;
        $item->save();
        $res = $item->toArray();

        if (empty($res)) {
            Log::debug('[stop] update status fail');
            throw new \Dingo\Api\Exception\ResourceException("update status fail");
        }

        return $this->resObjectGet($res, 'item', $request->path());

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

        $item = Item::find($params['id']);

        if (empty($item)) {
            Log::debug('[test] item not exist', $params);
            throw new \Dingo\Api\Exception\ResourceException(" item not exist");
        }
        $itemDetail = $item->toArray();

        if ($itemDetail['status'] == Item::STATUS_TESTING) {
            Log::debug('[test] status does not allow to update', $itemDetail);
            throw new \Dingo\Api\Exception\ResourceException(" status does not allow to update ");
        }

        $item->status = Item::STATUS_TESTING;
        $item->save();
        $res = $item->toArray();

        if (empty($res)) {
            Log::debug('[test] update status fail');
            throw new \Dingo\Api\Exception\ResourceException("update status fail");
        }

        $queueInfo = $this->__createQueue($res);

        $itemTestResult = InternalAPIService::post('/item/test_result', ['item_id' => $res['id'], 'item_run_log_id' => $queueInfo['item_run_log_id']]);

        if (empty($itemTestResult)) {
            Log::debug('[test] create item_test_result fail');
            throw new \Dingo\Api\Exception\ResourceException(" create item_test_result fail");
        }

        return $this->resObjectGet($res, 'item', $request->path());
    }

    /**
     * updateStatusTestSuccess
     * 任务测试成功状态修改
     *
     * @param id (任务ID)
     * @return array
     */
    public function updateStatusTestSuccess(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'id' => 'integer|required',
        ]);

        $item = Item::find($params['id']);

        if (empty($item)) {
            Log::debug('[updateStatusTestSuccess] item not exist', $params);
            throw new \Dingo\Api\Exception\ResourceException(" item not exist");
        }
        $itemDetail = $item->toArray();

        if ($itemDetail['status'] != Item::STATUS_TESTING) {
            Log::debug('[updateStatusTestSuccess] item status does not allow to testSucess' . $itemDetail['status']);
            throw new \Dingo\Api\Exception\ResourceException("item status does not allow to testSucess");
        }

        $item->status = Item::STATUS_TEST_SUCCESS;
        $item->save();
        $res = $item->toArray();

        return $this->resObjectGet($res, 'item', $request->path());
    }

    /**
     * updateStatusTestFail
     * 任务测试失败状态修改
     *
     * @param id (任务ID)
     * @return array
     */
    public function updateStatusTestFail(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'id' => 'integer|required',
        ]);

        $item = Item::find($params['id']);

        if (empty($item)) {
            Log::debug('[updateStatusTestFail] item not exist', $params);
            throw new \Dingo\Api\Exception\ResourceException(" item not exist");
        }
        $itemDetail = $item->toArray();

        if ($itemDetail['status'] != Item::STATUS_TESTING) {
            Log::debug('[updateStatusTestFail] item status does not allow to testFail', $itemDetail);
            throw new \Dingo\Api\Exception\ResourceException("item status does not allow to testFail");
        }

        $item->status = Item::STATUS_TEST_FAIL;
        $item->save();
        $res = $item->toArray();

        return $this->resObjectGet($res, 'item', $request->path());
    }

    /**
     * updateLastJobAt
     * 更新任务最后执行时间
     *
     * @param id (任务ID)
     * @return array
     */
    public function updateLastJobAt(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'id' => 'integer|required',
        ]);

        $item = Item::find($params['id']);


        if (empty($item)) {
            Log::debug('[updateLastJobAt] item not exist', $params);
            throw new \Dingo\Api\Exception\ResourceException(" item not exist");
        }
        $itemDetail = $item->toArray();

        $item->last_job_at = date("Y-m-d H:i:s");
        $item->save();
        $res = $item->toArray();

        return $this->resObjectGet($res, 'item', $request->path());
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
        $queueInfo = InternalAPIService::post('/queue_info/job', $formatParams);
        if (empty($queueInfo)) {
            Log::debug('[__createQueue] queue info job is error', $formatParams);
            throw new \Dingo\Api\Exception\ResourceException("queue info job is error");
        }
        return $queueInfo;

    }

    /**
     * delete
     * 任务删除
     *
     * @param id (任务ID)
     * @return array
     */
    public function delete(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'id' => 'integer|required',
        ]);

        $item = Item::find($params['id']);

        if (empty($item)) {
            Log::debug('[delete] item not exist', $params);
            throw new \Dingo\Api\Exception\ResourceException(" item not exist");
        }

        if ($item['status'] == Item::STATUS_START) {
            Log::debug('[delete] start item not allowed delete status=' . $item['status']);
            throw new \Dingo\Api\Exception\ResourceException(" start item not allowed delete");
        }

        $item->delete();

        $res = $item->toArray();
        return $this->resObjectGet($res['id'], 'item', $request->path());
    }

}
