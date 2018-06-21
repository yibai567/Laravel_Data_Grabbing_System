<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;

class SaveDataEvent extends Event
{
    use SerializesModels;

    /**
     * SaveData
     */
    public $messages;

    /**
     * 创建一个事件实例。
     *
     * @param $data
     * @return void
     */
    public function __construct($data)
    {
        $this->messages = $data;
    }
}
