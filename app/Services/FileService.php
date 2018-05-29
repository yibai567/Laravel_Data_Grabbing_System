<?php

namespace App\Services;

class FileService extends Service
{
    /**
     * create
     * 生成文件
     *
     * @param string $filename string $content
     * @return boolean
     */
    public function create($filename, $content = '')
    {
        //拼接路径和名称
        $filePath = config('script.file_generate_path');

        $file = $filePath . $filename;

        //写入文件
        file_put_contents($file, $content);

        //检查文件是否生成成功
        if (!file_exists($file)) {
            return false;
        }

        return true;
    }
}