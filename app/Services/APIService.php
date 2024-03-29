<?php

namespace App\Services;

use Log;
use Config;
use VDB\Spider\RequestHandler\GuzzleRequestHandler;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;

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

        try {
            $response = $dispatcher->post($url);
        } catch (\Dingo\Api\Exception\InternalHttpException $e) {

            $response = $e->getResponse();
            $errorMessage = $response->getContent();
            errorLog('[__dingoPost] ' . $errorMessage . 'url:' . $url);
            throw new \Dingo\Api\Exception\ResourceException('API post error');

        } catch (\App\Exceptions\Exception $e) {
            errorLog('[__dingoPost Exception API] ');
            throw new \Dingo\Api\Exception\ResourceException('API Exception error ');
        }

        return $response['data'];
    }

    public static function openGet($path, $params = [])
    {
        Config::set('logging.application_name', 'open_api');
        $url = config('url.jinse_open_url') . $path;
        $dispatcher = app('Dingo\Api\Dispatcher');

        try {
            $response = $dispatcher->get($url, $params);
        } catch (\Dingo\Api\Exception\InternalHttpException $e) {

            $response = $e->getResponse();
            $errorMessage = $response->getContent();
            errorLog('[__dingoPost] ' . $errorMessage . 'url:' . $url);
            throw new \Dingo\Api\Exception\ResourceException('API post error');

        } catch (\App\Exceptions\Exception $e) {
            errorLog('[__dingoPost Exception API] ');
            throw new \Dingo\Api\Exception\ResourceException('API Exception error ');
        }

        return $response['data'];
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

        try {
            $response = $dispatcher->post($url);
        } catch (\Dingo\Api\Exception\InternalHttpException $e) {

            $response = $e->getResponse();
            $errorMessage = $response->getContent();
            errorLog('[internalPost] ' . $errorMessage . 'url:' . $url);
            throw new \Dingo\Api\Exception\ResourceException('internal API post error');

        } catch (\App\Exceptions\Exception $e) {
            errorLog('[internalPost Exception API] ');
            throw new \Dingo\Api\Exception\ResourceException('internal API Exception error ');
        }

        return $response['data'];
    }

    public static function internalGet($path, $params = [])
    {
        Config::set('logging.application_name', 'internal_api');
        $url = config('url.jinse_internal_url') . $path;
        $dispatcher = app('Dingo\Api\Dispatcher');

        try {
            $response = $dispatcher->get($url, $params);
        } catch (\Dingo\Api\Exception\InternalHttpException $e) {

            $response = $e->getResponse();
            $errorMessage = $response->getContent();
            errorLog('[__dingoPost] ' . $errorMessage . 'url:' . $url);
            throw new \Dingo\Api\Exception\ResourceException('API post error');

        } catch (\App\Exceptions\Exception $e) {
            errorLog('[__dingoPost Exception API] ');
            throw new \Dingo\Api\Exception\ResourceException('API Exception error ');
        }

        return $response['data'];
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

        try {
            $response = $dispatcher->post($url);
        } catch (\Dingo\Api\Exception\InternalHttpException $e) {

            $response = $e->getResponse();
            $errorMessage = $response->getContent();
            errorLog('[__dingoPost] ' . $errorMessage . 'url:' . $url);
            throw new \Dingo\Api\Exception\ResourceException('API post error');

        } catch (\App\Exceptions\Exception $e) {
            errorLog('[__dingoPost Exception API] ');
            throw new \Dingo\Api\Exception\ResourceException('API Exception error ');
        }

        return $response['data'];
    }

    public static function baseGet($path, $params = [])
    {
        Config::set('logging.application_name', 'base_api');
        $url = config('url.jinse_base_url') . $path;
        $dispatcher = app('Dingo\Api\Dispatcher');

        try {
            $response = $dispatcher->get($url, $params);
        } catch (\Dingo\Api\Exception\InternalHttpException $e) {

            $response = $e->getResponse();
            $errorMessage = $response->getContent();
            errorLog('[__dingoPost] ' . $errorMessage . 'url:' . $url);
            throw new \Dingo\Api\Exception\ResourceException('API post error');

        } catch (\App\Exceptions\Exception $e) {
            errorLog('[__dingoPost Exception API] ');
            throw new \Dingo\Api\Exception\ResourceException('API Exception error ');
        }

        return $response['data'];
    }

    /**
     * get
     */
    public static function get($url, $params = [])
    {

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
    public static function post($url, $params = [])
    {
        $requestParams = [
            'timeout'  => 2,
            'debug' => false,
        ];
        $client = new Client($requestParams);

        try {
            $response = $client->request('POST', $url, ['json' => $params]);
            $resCode  = (string) $response->getStatusCode();
            $resBody  = $response->getbody()->getContents();

            if ($resBody == "ok" && $resCode == 200) {
                return true;
            }
        } catch (RequestException $e) {
            Log::error('[APIService post] error message = ' . $e->getMessage());
            throw new \Dingo\Api\Exception\ResourceException('post api error');
            // returnError(501, '调用接口失败');
        }

    }
}
