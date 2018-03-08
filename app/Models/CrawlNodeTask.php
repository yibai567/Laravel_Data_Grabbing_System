<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrawlNodeTask extends Model
{
    const IS_STARTUP = 1;
    const IS_STOP = 2;

    public $table = 't_crawl_node_task';

    public $fillable = [
        'node_id',
        'crawl_task_id',
        'cmd_startup',
        'cmd_stop',
        'log_path',
        'status',
        'start_on',
        'end_on',
    ];

}
