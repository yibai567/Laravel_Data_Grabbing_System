<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemTestResult extends Model
{
    use SoftDeletes;

    //初始化状态
    const STATUS_INIT = 1; // 初始化
    const STATUS_PROXY_TEST = 2; // 翻墙测试
    const STATUS_NO_PROXY_TEST = 3; // 不翻墙测试
    const STATUS_SUCCESS = 4; // 成功
    const STATUS_FAIL = 5; // 失败

    /**
     * 表名
     */
    protected $table = 't_item_test_result';

    /**
     * 隐藏的字段
     */
    protected $hidden = [
        "updated_at",
        "deleted_at",
    ];

    /**
     * 可更新字段
     */
    protected $fillable = [
        'item_id',
        'item_run_log_id',
        'short_contents',
        'md5_short_contents',
        'long_content0',
        'long_content1',
        'images',
        'error_message',
        'start_at',
        'end_at',
        'status'
    ];
}
