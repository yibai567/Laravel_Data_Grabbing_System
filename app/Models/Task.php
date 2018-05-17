<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes;

    //任务状态
    const STATUS_INIT = 0;
    const STATUS_START = 1;
    const STATUS_STOP = 2;

    protected $dates = ['deleted_at'];

    /**
     * 表名
     */
    protected $table = 't_task';

    /**
     * 可更新的字段
     */
    protected $fillable = [
        'script_id',
        'name',
        'description',
        'cron_type',
        'languages_type',
        'status',
    ];

    /**
     * 隐藏的字段
     */
    protected $hidden = [
        'deleted_at',
    ];
}
