<?php

namespace App\Service;

use Aliyun\Core\DefaultAcsClient;
use Aliyun\Core\Exception\ClientException;
use Aliyun\Core\Profile\DefaultProfile;
use Aliyun\Core\Regions\Endpoint;
use Aliyun\Core\Regions\EndpointConfig;
use Aliyun\Core\Regions\EndpointProvider;
use Aliyun\Core\Sms\Request\V20160927\SingleSendSmsRequest;
use Log;

class AliSMSService extends Service
{
    protected $__smsClint;

    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        $this->__init();
    }

    private function __init()
    {
        $access_key = config('aliyun.access_key');
        $access_secret = config('aliyun.access_secret');
        $endpointConfig = config('aliyun.sms.endpoint');
        $iClientProfile = DefaultProfile::getProfile($endpointConfig, $access_key, $access_secret);
        $client = new DefaultAcsClient($iClientProfile);

        $config = new EndpointConfig();
        $endpoint = new Endpoint($endpointConfig, $config->getRegionIds(), $config->getProductDomains());

        EndpointProvider::setEndpoints([$endpoint]);

        return $this->__smsClint = $client;
    }

    public function send($mobile, $tmpType, $params)
    {
        try {
            $smsConfig = config('aliyun.sms');

            $request = new SingleSendSmsRequest();
            $request->setSignName($smsConfig['sign']);  /*签名名称*/
            $request->setTemplateCode($smsConfig['template'][$tmpType]);  /*模板code*/
            $request->setRecNum($mobile);   /*目标手机号*/
            $request->setParamString(json_encode($params, true));   /*模板变量，数字一定要转换为字符串*/
            $response = $this->__smsClint->getAcsResponse($request);

            return true;
        } catch (ClientException  $e) {
            Log::error('AliSMSService send ClientException: '.$e->getErrorCode()."\t".$e->getErrorMessage());
        }

        return false;
    }

}