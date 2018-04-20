<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemResult extends Model
{
    use SoftDeletes;

    const STATUS_INIT = 1; // 初始化
    const STATUS_SUCCESS = 2; // 成功
    const STATUS_FAIL = 3; // 失败

    /**
     * 表名
     */
    protected $table = 't_item_result';

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
        'start_at',
        'end_at',
        'counter',
        'status'
    ];
}
