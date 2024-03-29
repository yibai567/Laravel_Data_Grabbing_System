<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller as BaseController;
use App\Services\FilterService;
use Config;
use App\Services\ItemService;

class Controller extends BaseController
{
    public function __construct()
    {
        Config::set('logging.application_name', 'open_api');
        $this->itemService = new ItemService();
    }
    /**
     * 格式化单条数据
     * @param $object
     * @param $type
     * @param null $url
     * @return \Illuminate\Http\JsonResponse
     */
    public function resObjectGet($object, $type, $url = null)
    {
        $service = new FilterService();
        $result = $service->filterResponseForGet($object, $type, $url);

        return response()->json($result);
    }

    /**
     * 格式化列表数据
     * @param $object
     * @param $type
     * @param null $url
     * @return \Illuminate\Http\JsonResponse
     */
    public function resObjectList($object, $type, $url = null)
    {
        $service = new FilterService();
        $result = $service->filterResponseForList($object, $type, $url);

        return response()->json($result);
    }

    /**
     * 返回错误信息
     * @param $status_code
     * @param $message
     * @param null $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function resError($status_code, $message, $data = null)
    {
        $result = [
            'status_code' => $status_code,
            'message' => $message,
            'data' => $data,
        ];

        return response()->json($result);
    }
}
