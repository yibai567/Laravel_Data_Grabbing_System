<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrawlNode extends Model
{
    const IS_USABLE = 1; //状态 可用
    const IS_DISABLE = 2; //状态 不可用

    public $table = 't_crawl_node';

}
