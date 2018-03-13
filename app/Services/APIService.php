<?php

namespace App\Services;

use Log;

class APIService extends Service
{
    /**
     * open API
     */

    public static function openPost($path, $params = [], $contentType = '')
    {
        $url = config('url.jinse_open_url') . $path;
        $dispatcher = app('Dingo\Api\Dispatcher');

        if ($contentType == 'json') {
            $dispatcher->json($params);
        } else {
            $dispatcher->with($params);
        }

        return $dispatcher->post($url);
    }

    public static function openGet($path, $params = [])
    {
        $url = config('url.jinse_open_url') . $path;
        $dispatcher = app('Dingo\Api\Dispatcher');

        return $dispatcher->get($url, $params);
    }

    /**
     * internal API
     */

    public static function internalPost($path, $params = [], $contentType = '')
    {
        $url = config('url.jinse_internal_url') . $path;
        $dispatcher = app('Dingo\Api\Dispatcher');

        if ($contentType == 'json') {
            $dispatcher->json($params);
        } else {
            $dispatcher->with($params);
        }

        return $dispatcher->post($url);
    }

    public static function internalGet($path, $params = [])
    {
        $url = config('url.jinse_internal_url') . $path;
        $dispatcher = app('Dingo\Api\Dispatcher');

        return $dispatcher->get($url, $params);
    }

    /**
     * base API
     */

    public static function basePost($path, $params = [], $contentType = '')
    {
        $url = config('url.jinse_base_url') . $path;
        $dispatcher = app('Dingo\Api\Dispatcher');

        if ($contentType == 'json') {
            $dispatcher->json($params);
        } else {
            $dispatcher->with($params);
        }

        return $dispatcher->post($url);
    }

    public static function baseGet($path, $params = [])
    {
        $url = config('url.jinse_base_url') . $path;
        $dispatcher = app('Dingo\Api\Dispatcher');

        return $dispatcher->get($url, $params);
    }

}
