<?php

namespace App\Services;

class FilterService extends Service
{
    /**
     * 格式化响应数据格式, 基于 restful 协议 List Method, 获取资源列表
     * @param $object array
     * @param $type
     * @param $url
     * @return array
     */
    public function filterResponseForList($object, $type, $url)
    {
        $result = [
            'status_code' => 200,
            'object' => 'list',
            'url' => $url,
            'has_more' => $object['has_more'],
            'data' => [],
        ];
        if (isset($object['total'])) {
            $result['total'] = $object['total'];
        }

        if (!empty($object['data']) && is_array($object['data'])) {
            foreach ($object['data'] as $key => $value) {
                $result['data'][$key] = $value;
            }
        }
        return $result;
    }

    /**
     * 格式化响应数据格式, 基于 restful 协议 GET Method, 获取指定资源
     * @param $object array
     * @param $type
     * @param $url
     * @param $list true/false
     * @return array|null
     */
    public function filterResponseForGet($object, $type, $url ,$list = false)
    {
        if (!is_array($object)) {
            $object = json_decode(json_encode($object), true);
        }

        if (!$list) {
            $result['status_code'] = 200;
            $result['object'] = $type;
            $result['url'] = $url;
            $result['data'] = $object;
            //$object = array_merge($result, $object);
        }

        return $result;
    }
}
