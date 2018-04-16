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
            "item_id" => "required|integer"
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
            "item_id" => "required|integer"
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
        if ($itemRunLog['status'] != 1) {
            throw new \Dingo\Api\Exception\ResourceException("invalid item_run_log status");
        }

        // 获取 item
        $item = InternalAPIService::get('/item', ['id' => $itemRunLog['item_id']]);
        if ($item['status'] != 4) {
            throw new \Dingo\Api\Exception\ResourceException("invalid item status");
        }

        // 判断 任务类型（test or production）、数据类型
        switch ($item['data_type']) {
            case 1:
                $path = '/item/result/html';
                if ($itemRunLog['type'] == 1) {
                    $path = '/item/test/result/html';
                }

                break;
            case 2:
                $path = '/item/result/json';
                if ($itemRunLog['type'] == 1) {
                    $path = '/item/test/result/json';
                }

                break;

            case 3:
                $path = '/item/result/image';
                if ($itemRunLog['type'] == 1) {
                    $path = '/item/test/result/image';
                }

                break;
            default:
                throw new \Dingo\Api\Exception\ResourceException("invalid item data_type");
        }

        try {
            $results = InternalAPIService::post($path, $params);
        } catch (\Dingo\Api\Exception\ResourceException $e) {

            // 标记当前任务为失败状态
            InternalAPIService::post('/item/run/log/fail', $params);
            throw new \Dingo\Api\Exception\ResourceException("invalid result");
        }

        // 任务结果的二次处理
        if (!empty($results)) {
            foreach ($results as $result) {
                // 判断任务结果是否需要截图
                $this->__captureImage($item, $result);

                // 判断任务是否需要图片资源
                $this->__downloadImage($item, $result);
            }
        }

        return $this->resObjectGet([], 'item_result', $request->path());
    }

    /**
     * __captureImage
     * 截图任务处理
     *
     * @param  $item
     * @param  $result
     * @return array
     */
    private function __captureImage($item, $result)
    {
        if ($item['data_type'] == 3) {
            return true;
        }

        if ($item['is_capture_image'] != 1) {
            return true;
        }

        if (empty($result['short_content']['detail_url'])) {
            return true;
        }

        $detailUrl    = $result['short_content']['detail_url'];
        $preDetailUrl = $item['pre_detail_url'];

        $itemParams = [
            'name'                => $item['name'] . '-截图',
            'resource_url'        => $detailUrl,
            'pre_detail_url'      => $preDetailUrl,
            'associate_result_id' => $result['id'],
            'is_proxy'            => $item['is_proxy'],
            'is_capture_image'    => 1,
            'type'                => 2,
            'data_type'           => 3,
            'content_type'        => 1,
            'cron_type'           => 1,
        ];

        // 添加系统截图任务
        $captureItem = InternalAPIService::post('/item/', $itemParams);

        // 入队列

        return true;
    }

    /**
     * __downloadImage
     * 获取图片资源处理
     *
     * @param  $item
     * @param  $result
     * @return array
     */
    private function __downloadImage($item, $result)
    {
        // 判断图片
        //
    }
}























