<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskStatistics extends Model
{
    use SoftDeletes;

    const TYPE_TASK = 1; //任务

    protected $dates = ['deleted_at'];

    /**
     * 表名
     */
    protected $table = 't_task_statistics';

    /**
     * 可更新的字段
     */
    protected $fillable = [
        'task_id',
        'last_job_at',
        'data_type',
        'is_proxy',
        'cron_type',
        'status',
        'total_result',
    ];

    /**
     * 隐藏的字段
     */
    protected $hidden = [
        'deleted_at',
    ];
}
