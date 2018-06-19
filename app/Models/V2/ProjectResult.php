<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectResult extends Model
{
    use SoftDeletes;

    /**
     * 表名
     */
    protected $table = 't_project_result';

    /**
     * 主键
     */
    protected $primaryKey = "id";

    /**
     * 隐藏的字段
     */
    protected $hidden = [
        "deleted_at",
        "updated_at",
    ];

    /**
     *
     * 可更新的字段
     */
    protected $fillable = [
        'content_type',
        'company',
        'task_id',
        'project_id',
        'task_run_log_id',
        'title',
        'md5_title',
        'md5_content',
        'content',
        'detail_url',
        'show_time',
        'author',
        'read_count',
        'thumbnail',
        'img_task_undone',
        'img_task_total',
        'screenshot',
        'status',
        'start_time',
        'end_time',
        'created_time',
    ];
}