<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrawlSetting extends Model
{
    protected $table = 't_crawl_task_setting';

    public function task()
    {
        return $this->belongsTo('App\Models\CrawlTask', 'crawl_task_id', 'id');
    }
}
