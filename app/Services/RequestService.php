<?php

namespace App\Services;

use Dompdf\Exception;
use Log;
use Config;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class RequestService extends Service
{
    private $__client;
    private $__userAgents = '';
    private $__urlSchema;
    private $__urlHost;
    private $__requestParams =  [
        'timeout'  => 30,
        'debug' => false,
    ];
    private $__proxy = '';

    public function __construct()
    {
        $this->__client = new Client();
    }

    /**
     * get
     * get 请求
     *
     * @param $url
     * @param array $params
     * @param string $type
     * @param array $header
     * @param bool $isProxy
     * @return array
     */
    public function get($url, $params=[], $header=[], $isProxy=false, $charset='UTF-8')
    {
        return $this->request('get', $url, $params, $header, $isProxy, $charset);
    }

    /**
     * post
     * post 请求
     *
     * @param $url
     * @param array $header
     * @param array $params
     * @param bool $isProxy
     * @return array
     */
    public function post($url, $params=[], $header=[], $isProxy=false, $charset='UTF-8')
    {
        return $this->request('post', $url, $params, $header, $isProxy, $charset);
    }

    /**
     * request
     * request 请求
     *
     * @param $type
     * @param $url
     * @param array $params
     * @param string $type
     * @param array $header
     * @param bool $isProxy
     * @return array
     */
    public function request($requestType, $url, $params=[], $header=[], $isProxy=false, $charset='UTF-8')
    {
        $this->__init($url, $header, $isProxy);
        try {
            if (!empty($params)) {
                $this->__setParams($requestType, $params);
            }
            $response = $this->__client->request($requestType, $url, $this->__requestParams);
            $resCode  = $response->getStatusCode();
            $resBody = $this->__formatData((string)$response->getBody(), $charset);
        } catch (Exception $e) {
            $resCode  = 1001;
            $resBody  = 'curl 请求异常 Exception getMessage = :' . $e->getMessage();
        }

        return [
            'status_code' => $resCode,
            'data' => $resBody,
        ];
    }

    /**
     * __header
     * 设置头信息
     *
     * @param array $header
     * @return array
     */
    private function __header($header=[])
    {
        $userAgent = $this->__userAgents[array_rand($this->__userAgents)];
        $headers = [
            'User-Agent' => $userAgent,
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Encoding' => 'gzip, deflate',
            'Accept-Language' => 'zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3',
            'Connection' => 'keep-alive',
            'Referer' => $this->__urlSchema . '://' . $this->__urlHost,
        ];

        if (is_array($header) && count($header) > 0) {
            $headers = array_merge($headers, $header);
        }
        $this->__requestParams['headers'] = $headers;
    }

    /**
     * __parse_url
     * 解析url地址
     *
     * @param $url
     * return array
     */
    private function __parse_url($url)
    {
        $urlArr = parse_url($url);

        if (!empty($urlArr)) {
            $this->__urlSchema = $urlArr['scheme'];
            $this->__urlHost = $urlArr['host'];
        }
        return false;
    }

    /**
     * __init
     * 初始化
     *
     * @param $url
     * @param array $params
     * @param array $header
     * @param bool $isProxy
     * @return array
     */
    private function __init($url, $header=[], $isProxy=false)
    {
        $this->__userAgents = config('header.user_agents');
        $this->__parse_url($url);
        if (!empty($header)) {
            $this->__header($header);
        }

        if ($isProxy) {
            $this->__requestParams['proxy'] = config('header.proxy');
        }
    }

    /**
     * __formatData
     * 编码格式
     *
     * @param $data
     * @param string $format
     * @return string
     */
    private function __formatData($data, $charset='UTF-8')
    {
        $format = mb_detect_encoding($data, ["UTF-8", "GB2312", "GBK", "EUC-CN"]);
        if ($format != $charset) {
            $data = iconv($format, $charset . '//IGNORE', $data);
        }

        return $data;
    }

    /**
     * __setParams
     * 设置参数
     *
     * @param string $requestType
     * @param $params
     * return null
     */
    private function __setParams($requestType='get', $params)
    {
        $requestType = strtoupper($requestType);
        if ($requestType == 'GET') {
            $this->__requestParams['query'] = $params;
        } elseif ($requestType == 'POST') {
            $this->__requestParams['json'] = $params;
        }
    }
}
