<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskRunLog extends Model
{
    use SoftDeletes;

    //状态
    const STATUS_SUCCESS = 1;   //成功
    const STATUS_FAIL = 2;  //失败

    const TYPE_TEST = 1; // 测试
    const TYPE_PRO = 2; // 生产

    protected $dates = ['deleted_at'];

    /**
     * 表名
     */
    protected $table = 't_task_run_log';

    /**
     * 可更新的字段
     */
    protected $fillable = [
        'task_id',
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
