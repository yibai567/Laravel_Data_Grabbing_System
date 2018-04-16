<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QueueInfo extends Model
{
    // 使用软删除
    use SoftDeletes;

    //type类型
    const TYPE_TEST = 1; // 测试
    const TYPE_PRO = 2; // 正式

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
