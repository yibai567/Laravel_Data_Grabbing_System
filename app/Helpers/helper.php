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
 * @params 参数
 */
if (!function_exists('infoLog')) {
    function infoLog($message, $params = [])
    {
        new Log();
        $extend = array(
            'params' => $params,
        );
        Log::info($message, $extend);
    }
}

if (!function_exists('otherLog')) {
    function otherLog($type = 'debug', $message)
    {
        new Log();
        Log::$type($message);
    }
}

/**
 * post请求封装
 */
if (!function_exists('dingoPost')) {
    function dingoPost($url, $params = [])
    {
        $dispatcher = app('Dingo\Api\Dispatcher');
        return $dispatcher->post($url, $params);
    }
}

/**
 * get请求封装
 */
if (!function_exists('dingoGet')) {
    function dingoGet($url, $params = [])
    {
        $dispatcher = app('Dingo\Api\Dispatcher');
        return $dispatcher->get($url, $params);
    }
}
