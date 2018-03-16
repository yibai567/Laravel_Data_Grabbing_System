<?php

namespace App\Services;

use Log;
use Config;
use VDB\Spider\RequestHandler\GuzzleRequestHandler;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class APIService extends Service
{
    /**
     * open API
     */

    public static function openPost($path, $params = [], $contentType = '')
    {
        Config::set('logging.application_name', 'open_api');
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
        Config::set('logging.application_name', 'open_api');
        $url = config('url.jinse_open_url') . $path;
        $dispatcher = app('Dingo\Api\Dispatcher');

        return $dispatcher->get($url, $params);
    }

    /**
     * internal API
     */

    public static function internalPost($path, $params = [], $contentType = '')
    {
        Config::set('logging.application_name', 'internal_api');
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
        Config::set('logging.application_name', 'internal_api');
        $url = config('url.jinse_internal_url') . $path;
        $dispatcher = app('Dingo\Api\Dispatcher');

        return $dispatcher->get($url, $params);
    }

    /**
     * base API
     */

    public static function basePost($path, $params = [], $contentType = '')
    {
        Config::set('logging.application_name', 'base_api');
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
        Config::set('logging.application_name', 'base_api');
        $url = config('url.jinse_base_url') . $path;
        $dispatcher = app('Dingo\Api\Dispatcher');

        return $dispatcher->get($url, $params);
    }

    /**
     * base API
     */

    public static function httpPost($url, $params = [], $contentType = '')
    {
        $dispatcher = app('Dingo\Api\Dispatcher');

        if ($contentType == 'json') {
            $dispatcher->json($params);
        } else {
            $dispatcher->with($params);
        }

        return $dispatcher->post($url);
    }

    /**
     * get
     */
    public static function get($url, $params = []) {

        try {
            $requestParams = [
                'timeout'  => 10000,
            ];
            $client = new Client($requestParams);
            $response = $client->request('GET', $url, ['query' => $params]);
            $resCode  = $response->getStatusCode();
            $resBody  = $response->getBody();
        } catch (Exception $e) {
            throw $e;
        }
        return $resBody;
    }
    /**
     * post
     */
    public static function post($url, $params = []) {
        try {
            $requestParams = [
                'timeout'  => 10000,
            ];
            $client = new Client($requestParams);
            $response = $client->request('POST', $url, ['form_params' => $params]);
            $resCode  = $response->getStatusCode();
            $resBody  = $response->getBody();
            dlogger('[api response body] = ' . json_encode($resBody), 'debug');
        } catch (Exception $e) {
            throw $e;
        }
        return $resBody;
    }
}
