<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;

class DataResultReportEvent extends Event
{
    use SerializesModels;

    /*
     * 原创对象
     */
    public $data;

    /**
     * 创建一个事件实例。
     *
     * @param  $data
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }
}