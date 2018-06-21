<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;

class SaveDataEvent extends Event
{
    use SerializesModels;

    /**
     * 原创对象
     */
    public $datas;

    /**
     * 创建一个事件实例。
     *
     * @param $data
     * @return void
     */
    public function __construct($datas)
    {
        $this->datas = $datas;
    }
}
