<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes;

    //脚本类型
    const LANGUAGES_TYPE_CASPERJS = 1;
    const LANGUAGES_TYPE_HTML = 2;
    const LANGUAGES_TYPE_API = 3;

    //任务执行类型 1、每分钟 2、每五分钟 3、每十分钟
    const CRON_TYPE_KEEP = 1;
    const CRON_TYPE_EVERY_FIVE_MINUTES = 2;
    const CRON_TYPE_EVERY_TEN_MINUTES = 3;

    //任务状态 0、初始化，1、启动，2停止

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

    public function taskStatistics()
    {
        return $this->hasOne('App\Models\TaskStatistics', 'task_id', 'id');
    }

}
