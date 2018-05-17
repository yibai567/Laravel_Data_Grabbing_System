<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskRunLog extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    /**
     * 表名
     */
    protected $table = 't_task_run_log';

    /**
     * 可更新的字段
     */
    protected $fillable = [
        'start_job_at',
        'end_job_at',
        'result_count',
        'status',
    ];

    /**
     * 隐藏的字段
     */
    protected $hidden = [
        'deleted_at',
    ];
}
