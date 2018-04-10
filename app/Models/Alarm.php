<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Alarm extends Model
{
    use SoftDeletes;

    const IS_INIT = 1; //初始化
    const IS_PROCESSING = 2; //处理中
    const IS_PROCESSED = 3; //已处理
    const IS_IGNORED = 4; //已忽略

    /**
     * 表名
     */
    protected $table = 't_alarm';

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
        'id',
        'alarm_rule_id',
        'crawl_task_id',
        'content',
        'operation_user',
        'operation_at',
        'completion_at',
        'status',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
}
