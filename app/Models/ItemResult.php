<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemResult extends Model
{
    use SoftDeletes;

    /**
     * 表名
     */
    protected $table = 't_item_test_result';

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

    protected $fillable = [
        'item_id',
        'item_run_log_id',
        'short_content',
        'md5_short_contents',
        'long_content0',
        'long_content1',
        'images',
        'start_at',
        'end_at',
        'status'
    ];
}
