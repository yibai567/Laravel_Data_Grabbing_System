<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrawlResult extends Model
{
    const IS_UNTREATED = 1; //未处理
    const IS_PROCESSED = 2; //已处理
    const EFFECT_DEFAULT = 1; //默认插入
    const EFFECT_TEST = 2;//test测试
    protected $table = 't_crawl_result';

    public $fillable = [
        'crawl_task_id',
        'original_data',
        'task_start_time',
        'task_end_time',
        'setting_selectors',
        'setting_keywords',
        'setting_data_type',
        'task_url',
        'format_data',
        'status',
    ];
}
