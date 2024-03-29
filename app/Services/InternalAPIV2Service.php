<?php

namespace App\Services;

use Log;
use Config;

class InternalAPIV2Service extends Service
{

    /**
     * post
     *
     * @param $path
     * @param $pararms
     * @param $contentType
     * @return $response['data']
     */
    public static function post($path, $params = [], $contentType = 'json')
    {
        $url = config('url.jinse_internal_url') . '/internalv2' . $path;
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
            Log::error("['internalv2 API post error'] " . $errorMessage);
            throw new \Dingo\Api\Exception\ResourceException('internalv2 API post error');

        } catch (\App\Exceptions\Exception $e) {
            throw new \Dingo\Api\Exception\ResourceException('internalv2 API Exception error ');
        }

        return $response['data'];
    }

    /**
     * post
     *
     * @param $path
     * @param $pararms
     * @return $response['data']
     */
    public static function get($path, $params = [])
    {
        $url = config('url.jinse_internal_url') . '/internalv2' . $path;
        $dispatcher = app('Dingo\Api\Dispatcher');

        try {
            $response = $dispatcher->get($url, $params);
        } catch (\Dingo\Api\Exception\InternalHttpException $e) {

            $response = $e->getResponse();
            $errorMessage = $response->getContent();
            Log::error("['internalv2 API get error'] " . $errorMessage);
            throw new \Dingo\Api\Exception\ResourceException('API get error');

        } catch (\App\Exceptions\Exception $e) {
            throw new \Dingo\Api\Exception\ResourceException('API Exception error ');
        }

        return $response['data'];
    }

}
