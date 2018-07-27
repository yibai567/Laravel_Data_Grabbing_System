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
    function sign($httpParams) {
        $dateTime = time();
        $accessKey = config('api_auth.access_secret');
        $secretKey = config('api_auth.secret');
        $httpParams['access_key'] = $accessKey;
        $httpParams['date'] = $dateTime;
        $signParams = array_merge($httpParams, array('secret_key' => $secretKey));
        ksort($signParams);
        $signString = http_build_query($signParams);
        $httpParams['sign'] = strtolower(md5($signString));
        return $httpParams;
    }
}

if (!function_exists('arrayRemovalDuplicate')){
    function arrayRemovalDuplicate($arr,$key){
        $result = array();
        foreach ($arr as $value) {
           if(!isset($result[$value[$key]])){
                $result[$value[$key]] = $value;
           }
        }
        return $result;
    }
}


if (!function_exists('returnError')){
    function returnError($status_code, $message, $data=[]){
        $result = [
            'status_code' => $status_code,
            'message' => $message,
            'data' => $data,
        ];
        echo json_encode($result);
        exit();
    }
}

if (!function_exists('jsonFormat')) {
    function jsonFormat($data, $indent=null){

    // 将urlencode的内容进行urldecode
    $data = urldecode($data);

    // 缩进处理
    $ret = '';
    $pos = 0;
    $length = strlen($data);
    $indent = isset($indent)? $indent : '&nbsp;&nbsp;&nbsp;&nbsp';
    $newline = "\n";
    $prevchar = '';
    $outofquotes = true;

    for($i=0; $i<=$length; $i++){

        $char = substr($data, $i, 1);

        if($char=='"' && $prevchar!='\\'){
            $outofquotes = !$outofquotes;
        }elseif(($char=='}' || $char==']') && $outofquotes){
            $ret .= $newline;
            $pos --;
            for($j=0; $j<$pos; $j++){
                $ret .= $indent;
            }
        }

        $ret .= $char;

        if(($char==',' || $char=='{' || $char=='[') && $outofquotes){
            $ret .= $newline;
            if($char=='{' || $char=='['){
                $pos ++;
            }

            for($j=0; $j<$pos; $j++){
                $ret .= $indent;
            }
        }

        $prevchar = $char;
    }
        return $ret;
    }
}

/**
 * formatShowTime
 *
 * @param
 * @return int
 */
if (!function_exists('formatShowTime')) {
    function formatShowTime($time)
    {
        if(strtotime(date('Y-m-d H:i:s',$time)) === $time) {
            return $time;
        }

        $showTime = strtotime($time);

        if ($showTime) {
            return $showTime;
        }

        if (preg_match('/^\d+分钟/', $time)) {
            preg_match('/^\d+/', $time, $result);
            return time() - $result[0] * 60;
        } elseif (preg_match('/^\d+小时/', $time)) {
            preg_match('/^\d+/', $time, $result);
            return time() - $result[0] * 60 * 60;
        } elseif (preg_match('/^\d+秒/', $time)) {
            preg_match('/^\d+/', $time, $result);
            return time() - $result[0];
        } elseif (preg_match('/^\d+天/', $time)) {
            preg_match('/^\d+/', $time, $result);
            return time() - $result[0] * 60 * 60 * 24;
        }

        return 0;
    }
}




