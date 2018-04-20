<?php

namespace App\Http\Controllers\API\V1;

use Illuminate\Http\Request;
use Log;
use App\Services\ValidatorService;
use App\Services\InternalAPIService;
use App\Models\ItemRunLog;
use App\Models\Item;
use App\Models\ItemTestResult;
use App\Models\QueueInfo;
use Redis;

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

    /*
     * getLastTestResult
     * 获取最后一次测试结果
     *
     * @param item_id
     * @return array
     */
    public function getTestResult(Request $request)
    {
        $params = $request->all();
        ValidatorService::check($params, [
            "item_id" => "required|integer"
        ]);

        $result = InternalAPIService::get('/item/test_result', $params);
        return $this->resObjectGet($result, 'item_result', $request->path());
    }

    /**
     * dispatchJob
     * 结果分发
     *
     * @param item_run_log_id (执行记录 id)
     * @param start_at (开始时间)
     * @param end_at (结束时间)
     * @param result (抓取结果)
     * @return array
     */
    public function dispatchJob(Request $request)
    {
        $params = $request->all();
        ValidatorService::check($params, [
            'item_run_log_id' => 'required|integer|min:1|max:99999999',
            'start_at'      => 'date|nullable',
            'end_at'        => 'date|nullable',
            'short_contents'   => 'array|nullable',
            'long_contents'    => 'array|nullable',
            'images'          => 'nullable',
            'error_message'   => 'string|nullable'
        ]);
Log::debug('[dispatchJob] 请求', $params);
exit();
        // 获取 item run Log
        Log::debug('[dispatchJob] 请求 /item_run_log', ['id' => $params['item_run_log_id']]);
        $itemRunLog = InternalAPIService::get('/item_run_log', ['id' => $params['item_run_log_id']]);
        if ($itemRunLog['status'] != ItemRunLog::STATUS_RUNNING) {
            throw new \Dingo\Api\Exception\ResourceException("invalid item_run_log status");
        }

        // 获取 item
        Log::debug('[dispatchJob] 请求 /item', ['id' => $itemRunLog['item_id']]);
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
                    $path = '/item/test_result/html';
                }

                break;
            case Item::DATA_TYPE_JSON:
                $path = '/item/result/json';
                if ($itemRunLog['type'] == ItemRunLog::TYPE_TEST) {
                    $path = '/item/test/result/json';
                }

                break;

            case Item::DATA_TYPE_CAPTURE:
                $path = '/item/result/image';
                if ($itemRunLog['type'] == ItemRunLog::TYPE_TEST) {
                    $path = '/item/test/result/image';
                }

                break;
            default:
                throw new \Dingo\Api\Exception\ResourceException("invalid item data_type");
        }
        try {
            $params['is_proxy'] = $item['is_proxy'];
            $params['item_id'] = $item['id'];
            Log::debug('[dispatchJob] 请求 ' . $path);
            // Log::debug('[dispatchJob] 请求 ' . $path, $params);  // 完整数据太多
            $results = InternalAPIService::post($path, $params);
        } catch (\Dingo\Api\Exception\ResourceException $e) {
            // 标记当前任务为失败状态
            Log::debug('[dispatchJob] 请求 /item_run_log/status/fail', $params);
            InternalAPIService::post('/item_run_log/status/fail', $params);

            throw new \Dingo\Api\Exception\ResourceException("invalid result");
        }
        // 任务结果的二次处理
        if (!empty($results)) {
            foreach ($results as $result) {
                // 判断任务结果是否需要截图
                $this->__captureImage($item, $result, $itemRunLog['type']);

                // 判断任务是否需要图片资源
                $this->__downloadImage($item, $result);
            }
        } else {
            if ($itemRunLog['type'] == ItemRunLog::TYPE_TEST) {
                $this->__testResult($itemRunLog, $item);
            }
        }

        // return $this->resObjectGet([], 'item_result', $request->path());
    }

    /**
     * __captureImage
     * 截图任务处理
     *
     * @param  $item
     * @param  $result
     * @return array
     */
    private function __captureImage($item, $result, $runLogType)
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
        Log::debug('[dispatchJob __captureImage] 请求 /item', $itemParams);
        $captureItem = InternalAPIService::post('/item', $itemParams);

        $isTest = QueueInfo::TYPE_PRO;
        if ($runLogType == ItemRunLog::TYPE_TEST) {
            $isTest = QueueInfo::TYPE_TEST;
        }

        // 入队列
        $jobParams = [
            'id' =>  config('crawl_queue.' . $isTest . '.' . $item['data_type'] . '.' . $item['is_proxy']),
            'item_id' => $captureItem->id
        ];
        Log::debug('[dispatchJob __captureImage] 请求 /queue_info/job', $jobParams);
        $queueItem = InternalAPIService::post('/queue_info/job', $jobParams);

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
        if (!array_key_exists("images", $result) || empty($result['images'])) {
            return true;
        }

        $is_test = false;
        if ($item['status'] == Item::STATUS_TESTING) {
            $is_test = true;
        }

        if (array_key_exists("remove_images", $result) && !empty($result['remove_images'])) {
            if (in_array($result['images'], $result['remove_images'])) {
                $data = [
                    'id' => $result['id'],
                    'resource_url' => $result['images'],
                    'is_test' => $is_test
                ];
                Log::debug('[dispatchJob __downloadImage] 入队列 crawl_image_queue', $data);
                Redis::connection('queue')->lpush('crawl_image_queue', json_encode($data));
            }
        } else {
            // 图片上传
            $data = [
                'id' => $result['id'],
                'resource_url' => $result['images'],
                'is_test' => $is_test
            ];
            Log::debug('[dispatchJob __downloadImage] 入队列 crawl_image_queue', $data);
            Redis::connection('queue')->lpush('crawl_image_queue', json_encode($data));
        }
    }

    /**
     * __testResult()
     * 获取图片资源处理
     *
     * @param  $itemRunLog
     * @param  $item
     * @return array
     */
    private function __testResult($itemRunLog, $item)
    {
        // 获取 test_result 结果
        Log::debug('[dispatchJob __testResult] 请求 /item/test_result', ['item_run_log_id' => $itemRunLog['id']]);
        $test_result = InternalAPIService::get('/item/test_result', ['item_run_log_id' => $itemRunLog['id']]);
        switch ($test_result['status']) {
            case ItemTestResult::STATUS_PROXY_TEST_FAIL:
                // 修改 proxy 字段
                $changeProxy = [
                    'is_proxy' => Item::IS_PROXY_NO,
                    'id' => $itemRunLog['item_id']
                ];
                Log::debug('[dispatchJob __testResult] 翻墙测试失败, item/update 修改 proxy 为不翻墙', $changeProxy);
                InternalAPIService::post('/item/update', $changeProxy);

                // 重新测试
                Log::debug('[dispatchJob __testResult] 重新测试, /item/test', ['id' => $itemRunLog['item_id']]);
                InternalAPIService::post('/item/test', ['id' => $itemRunLog['item_id']]);
                break;

            case ItemTestResult::STATUS_NO_PROXY_TEST_FAIL:
                // 修改 proxy 字段
                $changeProxy = [
                    'is_proxy' => Item::IS_PROXY_YES,
                    'id' => $itemRunLog['item_id']
                ];
                Log::debug('[dispatchJob __testResult] 不翻墙测试失败, item/update 修改 proxy 为翻墙', $changeProxy);
                InternalAPIService::post('/item/update', $changeProxy);

                // 重新测试
                Log::debug('[dispatchJob __testResult] 重新测试, /item/test', ['id' => $itemRunLog['item_id']]);
                InternalAPIService::post('/item/test', ['id' => $itemRunLog['item_id']]);
                break;

            case ItemTestResult::STATUS_FAIL:
                // 修改任务状态到测试失败
                Log::debug('[dispatchJob __testResult] 请求 /item/status/test_fail', ['id' => $itemRunLog['item_id']]);
                InternalAPIService::post('/item/status/test_fail', ['id' => $itemRunLog['item_id']]);

                // 修改run_log到失败
                Log::debug('[dispatchJob __testResult] 请求 /item_run_log/status/fail', ['id' => $itemRunLog['id']]);
                InternalAPIService::post('/item_run_log/status/fail', ['id' => $itemRunLog['id']]);
                break;

            default:
                break;
        }
    }
}