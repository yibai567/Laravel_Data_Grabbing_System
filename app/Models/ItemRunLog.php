<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemRunLog extends Model
{
    /**
     * 使用软删除
     */
    use SoftDeletes;

    const TYPE_TEST = 1; // 测试
    const TYPE_PRO = 2; // 产品

    protected $dates = ['deleted_at'];

    /**
     * 表名
     */
    protected $table = 't_item_run_log';

    /**
     * 可更新字段
     */
    protected $fillable = [
        'item_id',
        'type',
        'start_at',
        'end_at',
        'status',
    ];

    /**
     * 隐藏字段
     */
    protected $hidden = [
        'deleted_at',
    ];
}
