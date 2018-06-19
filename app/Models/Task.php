<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes;

    //脚本类型
    const DATA_TYPE_CASPERJS = 1;
    const DATA_TYPE_TYPE_HTML = 2;
    const DATA_TYPE_TYPE_API = 3;

    //任务执行类型 1、每分钟 2、每五分钟 3、每十分钟
    const CRON_TYPE_KEEP = 1;
    const CRON_TYPE_EVERY_FIVE_MINUTES = 2;
    const CRON_TYPE_EVERY_TEN_MINUTES = 3;

    //任务状态 1、初始化，2、启动

    const STATUS_INIT = 1;
    const STATUS_START = 2;

    protected $dates = ['deleted_at'];

    /**
     * 表名
     */
    protected $table = 't_task';

    /**
     *
     * 可更新的字段
     */
    protected $fillable = [
        'script_id',
        'name',
        'description',
        'cron_type',
        'data_type',
        'status',
    ];

    /**
     * 隐藏的字段
     */
    protected $hidden = [
        'deleted_at',
    ];

    public function taskStatistics()
    {
        return $this->hasOne('App\Models\TaskStatistics', 'task_id', 'id');
    }

    public function script()
    {
        return $this->belongsTo('App\Models\Script', 'script_id', 'id');
    }

}
