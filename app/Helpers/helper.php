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
    public function createLog($filename = null, $content='')
    {
        if(file_put_contents($filename, $content,FILE_APPEND)){
            return  "写入成功。<br />";
        }
        if($data = file_get_contents($file)){
            return  "写入文件的内容是：$data";
        }
    }
