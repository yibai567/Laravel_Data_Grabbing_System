<?php
/**
 * Created by PhpStorm.
 * User: Pascal
 * Date: 2018/2/9
 * Time: 下午5:11
 */

if (!function_exists('generateScript')) {
    function generateScript($filename = null, $content='') {
        if (!is_dir(dirname($filename))) {
            mkdir(dirname($filename));
        }
        file_put_contents($filename, $content);
        return true;
    }
}

/**
 * 记录信息日志
 * @message 内容描述
 * @fields 参数
 */
if (!function_exists('infoLog')) {
    function infoLog($message, $fields = [])
    {
        new Log();
        $extend = array(
            'fields' => $fields,
            'application_name' => config('logging.application_name'),
        );
        Log::info($message, $extend);
    }
}

if (!function_exists('errorLog')) {
    function errorLog($message, $fields = [])
    {
        new Log();
        $extend = array(
            'fields' => $fields,
            'application_name' => config('logging.application_name'),
        );
        Log::error($message, $extend);
    }
}

if (!function_exists('otherLog')) {
    function otherLog($type = 'debug', $message)
    {
        new Log();
        Log::$type($message);
    }
}

if (!function_exists('decodeUnicode')) {
    function decodeUnicode($str) {
        return preg_replace_callback('/\\\\u([0-9a-f]{4})/i',
            create_function('$matches', 'return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");'), $str);
    }
}

if (!function_exists('sign')) {
    function sign() {
        $accessKey = config('api_auth.access_secret');
        $secretKey = config('api_auth.secret');
        $httpParams = [
            'access_key' => $accessKey,
            'date' => time()
        ];

        $signParams = array_merge($httpParams, array('secret_key' => $secretKey));

        ksort($signParams);
        $signString = http_build_query($signParams);

        $sign = strtolower(md5($signString));
        return $sign;
    }
}
