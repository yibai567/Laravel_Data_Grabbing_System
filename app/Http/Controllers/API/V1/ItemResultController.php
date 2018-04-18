<?php

namespace App\Http\Controllers\API\V1;

use Illuminate\Http\Request;
use Log;
use App\Services\ValidatorService;
use App\Services\InternalAPIService;
use App\Models\ItemRunLog;
use App\Models\Item;

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
            "item_run_log_id" => "required|integer"
        ]);

        $result = InternalAPIService::get('/item/test/result', $params);
        return $this->resObjectGet($result, 'item_result', $request->path());
    }

    /**
     * dispatchJob
     * 结果分发
     *
     * @param item_run_log_id (执行记录 id)
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
            'error_message'   => 'string|nullable'
        ]);
        // 获取 item run Log
        $itemRunLog = InternalAPIService::get('/item_run_log', ['id' => $params['item_run_log_id']]);
        if ($itemRunLog['status'] != ItemRunLog::STATUS_RUNNING) {
            throw new \Dingo\Api\Exception\ResourceException("invalid item_run_log status");
        }

        // 获取 item
        $item = InternalAPIService::get('/item', ['id' => $itemRunLog['item_id']]);

        // // 任务状态应该是 2 、5
        // if ($item['status'] != 4) {
        //     throw new \Dingo\Api\Exception\ResourceException("invalid item status");
        // }

        // 判断 任务类型（test or production）、数据类型
        switch ($item['data_type']) {
            case Item::DATA_TYPE_HTML:
                $path = '/item/result/update';
                if ($itemRunLog['type'] == ItemRunLog::TYPE_TEST) {
                    $path = '/item/test/result/update';
                }

                break;
            // case Item::DATA_TYPE_JSON:
            //     $path = '/item/result/json';
            //     if ($itemRunLog['type'] == ItemRunLog::TYPE_TEST) {
            //         $path = '/item/test/result/json';
            //     }

            //     break;

            // case Item::DATA_TYPE_CAPTURE:
            //     $path = '/item/result/image';
            //     if ($itemRunLog['type'] == ItemRunLog::TYPE_TEST) {
            //         $path = '/item/test/result/image';
            //     }

            //     break;
            default:
                throw new \Dingo\Api\Exception\ResourceException("invalid item data_type");
        }
        try {
            $params['is_proxy'] = $item['is_proxy'];
            dd($path);
            $results = InternalAPIService::post($path, $params);
            dd($results);
        } catch (\Dingo\Api\Exception\ResourceException $e) {
dd(1);
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
        } else {
            // if ($item['status'])
            // if is_test
            //     get test result
            //         status
            //             1
            //             2
            //                 // no porxy
            //                 // add redis

            //             3
            //             4
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
        if ($item['data_type'] == Item::DATA_TYPE_CAPTURE) {
            return true;
        }

        if ($item['is_capture_image'] != Item::IS_CAPTURE_IMAGE_TRUE) {
            return true;
        }

        if (!isset($result['short_content']['detail_url']) || empty($result['short_content']['detail_url'])) {
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
            'is_capture_image'    => Item::IS_CAPTURE_IMAGE_TRUE,
            'type'                => Item::TYPE_SYS,
            'data_type'           => Item::DATA_TYPE_CAPTURE,
            'content_type'        => Item::CONTENT_TYPE_SHORT,
            'cron_type'           => Item::CRON_TYPE_ONLY_ONE,
        ];

        // 添加系统截图任务
        $captureItem = InternalAPIService::post('/item', $itemParams);

        // 入队列
        // $queueItem = InternalAPIService::post('/queue_info/job', )
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























