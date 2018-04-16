<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QueueInfo extends Model
{
    // 使用软删除
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    /**
     * 表名
     */
    protected $table = 't_queue_info';

    /**
     * 隐藏的字段
     */
    protected $hidden = [
        'deleted_at',
    ];
}
