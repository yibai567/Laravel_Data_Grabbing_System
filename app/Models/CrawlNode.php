<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrawlNode extends Model
{
    const IS_USABLE = 1; //状态 可用
    const IS_DISABLE = 2; //状态 不可用

    public $table = 't_crawl_node';

    public function crawlNodeTasks()
    {
        return $this->hasMany('App\Models\CrawlNodeTask', 'node_id', 'id')
            ->where('status', '=', CrawlNodeTask::IS_STARTUP);
    }
}
