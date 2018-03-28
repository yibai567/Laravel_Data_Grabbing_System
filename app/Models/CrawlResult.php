<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrawlResult extends Model
{
    use SoftDeletes;

    //未处理
    const IS_UNTREATED = 1;

     //已处理
    const IS_PROCESSED = 2;

    //上报失败
    const IS_REPORT_ERROR = 3;

    //test测试
    const EFFECT_TEST = 1;

    const IS_TEST_TRUE = 1;

    const IS_TEST_FALSE = 2;

    /**
     * 表名
     */
    protected $table = 't_crawl_result_v2';

    /**
     * 主键
     */
    protected $primaryKey = "id";

    /**
     * 隐藏的字段
     */
    protected $hidden = [
        "updated_at",
        "deleted_at",
    ];

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
