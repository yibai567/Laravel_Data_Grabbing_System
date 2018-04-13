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
    public function paramsVerifyRule() {
        return [
            "data_type" => "required|integer|between:1,2",
            "content_type" => "required|integer|between:1,2",
            "resource_url" => "required|string",
            "is_capture_image" => "nullable|integer|between:1,2",
            "cron_type" => "nullable|integer|in:1,2,3,4",
            "is_proxy" => "nullable|integer|between:1,2",
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
            "data_type" => "nullable|integer|between:1,2",
            "content_type" => "nullable|integer|between:1,2",
            "resource_url" => "nullable|string",
            "is_capture_image" => "nullable|integer|between:1,2",
            "cron_type" => "nullable|integer|in:1,2,3,4",
            "is_proxy" => "nullable|integer|between:1,2",
        ];
    }

    public function paramsFormat($data) {

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

        if (!empty($formatParams['short_content_selector'])) {
            //短内容不为空时，detail_url键名不存在，默认键名
            if (!array_key_exists('detail_url', $formatParams['short_content_selector'])) {
                $formatParams['short_content_selector']['detail_url'] = "";
            }

            //如果is_capture_image is true时，detail_url不能为空
            if ($formatParams['is_capture_image'] == Item::IS_CAPTURE_IMAGE_TRUE) {
                if (empty($formatParams['short_content_selector']['detail_url'])) {
                    throw new \Dingo\Api\Exception\ResourceException("is_capture_image is true detail_url not null");
                }
            }

            $formatParams['short_content_selector'] = json_encode($formatParams['short_content_selector']);
        }
        return $formatParams;
    }
}
