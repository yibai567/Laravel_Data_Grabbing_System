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
            throw $e;
        }
        return $resBody;
    }
    /**
     * post
     */
    public function post($url, $params = [])
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
            throw new \Dingo\Api\Exception\ResourceException('post api error');
            // returnError(501, '调用接口失败');
        }

    }
}
