<?php

namespace App\Services;

use Log;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class HttpService extends Service
{

    /**
     * get
     */
    public function get($url, $params = [])
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
            Log::error('[HttpService->get]' . $e->getMessage());
            throw new \Dingo\Api\Exception\ResourceException('get api error');
        }
        return $resBody;
    }
    /**
     * post
     */
    public function post($url, $params = [])
    {
        $requestParams = [
            'timeout'  => 30,
            'debug' => false,
        ];
        $client = new Client($requestParams);

        try {
            $response = $client->request('POST', $url, ['json' => $params]);
            $resCode  = (string) $response->getStatusCode();
            $resBody  = $response->getbody()->getContents();
        } catch (RequestException $e) {
            Log::error('[HttpService->post]' . $e->getMessage());
            throw new \Dingo\Api\Exception\ResourceException('post api error');
        }

        return json_decode($resBody, true);

    }
    public function postV1($url, $params = [])
    {
        $requestParams = [
            'timeout'  => 2,
            'debug' => false,
        ];
        $client = new Client($requestParams);

        try {
            $response = $client->request('POST', $url, ['json' => $params]);
            $resCode  = (string) $response->getStatusCode();
            $resBody  = $response->getbody();
        } catch (RequestException $e) {
            Log::error('[HttpService->post]' . $e->getMessage());
            throw new \Dingo\Api\Exception\ResourceException('post api error');
        }
        return $resBody;
    }
}
