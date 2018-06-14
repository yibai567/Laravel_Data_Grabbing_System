<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskProjectMap extends Model
{
    use SoftDeletes;

    /**
     * 表名
     */
    protected $table = 't_task_project_map';

    /**
     *
     * 可更新的字段
     */
    protected $fillable = [
        'task_id',
        'project_id'
    ];

    /**
     * 隐藏的字段
     */
    protected $hidden = [
        'deleted_at',
    ];
}