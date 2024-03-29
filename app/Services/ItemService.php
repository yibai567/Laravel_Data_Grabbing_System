<?php

namespace App\Services;

use Log;
use Config;
use App\Models\Item;


class ItemService extends Service
{

    /**
     * createParamsVerify
     * 任务创建参数验证
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
    public function verifyParamsRule() {
        return [
            "data_type" => "required|integer|in:1,2,3",
            "content_type" => "required|integer|between:1,2",
            "resource_url" => "required|string",
            "is_capture_image" => "nullable|integer|between:1,2",
            "cron_type" => "nullable|integer|in:1,2,3,4,5",
            "is_proxy" => "nullable|integer|between:1,2",
            "short_content_selector" => "nullable",
            "long_content_selector" => "nullable",
            "row_selector" => "nullable|string|max:100",
            "header" => "nullable",
        ];
    }

    /**
     * updateParamsVerify
     * 任务修改参数验证
     *
     * @param id (任务id)
     * @param data_type (数据类型 1 html | 2 json)
     * @param content_type (内容类型 1 短内容 | 2 内容)
     * @param resource_url (资源URL)
     * @param is_capture_image (是否截取图片 1 true | 2 false)
     * @param cron_type (执行频次 1 持续执行, 2 每分钟执行一次, 3 每小时执行一次, 4 每天执行一次)
     * @param is_proxy (是否翻墙 1 翻墙 | 2 不翻墙)
     * @return array
     */
    public function updateParamsVerifyRule() {
        return [
            "id" => "required|integer",
            "data_type" => "nullable|integer|in:1,2,3",
            "content_type" => "nullable|integer|between:1,2",
            "resource_url" => "nullable|string",
            "is_capture_image" => "nullable|integer|between:1,2",
            "cron_type" => "nullable|integer|in:1,2,3,4,5",
            "is_proxy" => "nullable|integer|between:1,2",
            "type" => "nullable|integer|between:1,2",
            "action_type" => "nullable|integer|between:1",
            "associate_result_id" => "nullable|integer",
            "pre_detail_url" => "nullable|string",
            "short_content_selector" => "nullable",
            "long_content_selector" => "nullable",
            "row_selector" => "nullable|string",
            "header" => "nullable",
            "last_job_at" => "nullable|date",
            "status" => "nullable|between:1,6",
        ];
    }

    /**
     * paramsFormat
     * 任务默认参数格式化
     *
     * @param $data (任务参数)
     * @return array
     */
    public function formatParams($data) {
        $formatParams = $data;
        $item = [
            'type'  => 1,
            'action_type' => 1,
            'cron_type' => 1,
            'is_proxy' => 2,
        ];

        foreach ($item as $key => $value) {
            if (empty($formatParams[$key])) {
                $formatParams[$key] = $value;
            }
        }
        return $this->verifySelector($formatParams);
    }

    /**
     * verifySelector
     * 验证任务选择器
     *
     * @param $data (任务参数)
     * @return array
     */
    public function verifySelector($data) {
        $item = [
            'name'  => '',
            'pre_detail_url' => '',
            'short_content_selector' => '',
            'long_content_selector' => '',
            'row_selector' => '',
            'header' => '',
        ];
        foreach ($item as $key => $value) {
            if (empty($data[$key])) {
                $data[$key] = $value;
            }
        }
        if (!empty($data['short_content_selector'])) {
            $data['short_content_selector'] = json_decode($data['short_content_selector'], true);
            //短内容不为空时，detail_url键名不存在，默认键名
            if (isset($data['short_content_selector']['detail_url']) && empty($data['short_content_selector']['detail_url'])) {
                $data['short_content_selector']['detail_url'] = "";
            }

            //如果is_capture_image is true时，detail_url不能为空
            if ($data['is_capture_image'] == Item::IS_CAPTURE_IMAGE_TRUE) {
                if (empty($data['short_content_selector']['detail_url'])) {

                    Log::debug('[verifySelector] is_capture_image 为true, detail_url 不能为空', $data);
                    throw new \Dingo\Api\Exception\ResourceException("is_capture_image is true detail_url not null");
                }
            }

            $data['short_content_selector'] = json_encode($data['short_content_selector']);

            if (strlen($data['short_content_selector']) > 1000) {
                Log::debug('[verifySelector] short_content_selector 大小不能超过 1000' . strlen($data['short_content_selector']));
                throw new \Dingo\Api\Exception\ResourceException("short_content_selector too long ");
            }
        }
        if (!empty($data['long_content_selector'])) {
            $data['long_content_selector'] = json_decode($data['long_content_selector'], true);

            //短内容不为空时，detail_url键名不存在，默认键名
            if (empty($data['long_content_selector']['detail_url'])) {
                $data['long_content_selector']['detail_url'] = "";
            }

            //如果is_capture_image is true时，detail_url不能为空
            if ($data['is_capture_image'] == Item::IS_CAPTURE_IMAGE_TRUE) {
                if (empty($data['long_content_selector']['detail_url'])) {

                    Log::debug('[verifySelector] is_capture_image 为true, detail_url 不能为空', $data);
                    throw new \Dingo\Api\Exception\ResourceException("is_capture_image is true detail_url not null");
                }
            }

            $data['long_content_selector'] = json_encode($data['long_content_selector']);

            if (strlen($data['long_content_selector']) > 1000) {
                Log::debug('[verifySelector] long_content_selector 大小不能超过 1000' . strlen($data['long_content_selector']));
                throw new \Dingo\Api\Exception\ResourceException("long_content_selector too long ");
            }
        }

        if (!empty($data['header'])) {
            if (strlen($data['header']) > 1000) {
                Log::debug('[verifySelector] header 大小不能超过 1000' . strlen($data['header']));
                throw new \Dingo\Api\Exception\ResourceException("header too long ");
            }
        }
        return $data;
    }
}
