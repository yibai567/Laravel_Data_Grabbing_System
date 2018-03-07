<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrawlSetting extends Model
{
    protected $table = 't_crawl_task_setting';
    const TYPE_GENERAL = 1; //通用模版类型
    const TYPE_CUSTOM = 2; //自定义模版类型
    public function task()
    {
        return $this->belongsTo('App\Models\CrawlTask', 'crawl_task_id', 'id');
    }
}
