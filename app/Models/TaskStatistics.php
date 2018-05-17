<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskStatistics extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    /**
     * 表名
     */
    protected $table = 't_task_statistics';

    /**
     * 可更新的字段
     */
    protected $fillable = [
        'last_job_at',
        'run_times',
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
