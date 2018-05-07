<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Script extends Model
{
    use SoftDeletes;

    //初始化状态
    const STATUS_INIT = 1; // 初始化
    const STATUS_GENERATE = 2; // 已生成

    //任务执行类型 1、保持 2、每分钟 3、每五分钟 4、每十分钟 5、每十五分钟 6、每二十分钟
    const CRON_TYPE_KEEP = 1;
    const CRON_TYPE_EVERY_MINUTE = 2;
    const CRON_TYPE_EVERY_FIVE_MINUTES = 3;
    const CRON_TYPE_EVERY_TEN_MINUTES = 4;
    const CRON_TYPE_EVERY_FIFTEEN_MINUTES = 5;
    const CRON_TYPE_EVERY_TWENTY_MINUTES = 6;

    /**
     * 表名
     */
    protected $table = 't_script';

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
        'name',
        'description',
        'script_init_id',
        'step',
        'cron_type',
        'last_generate_at',
        'status',
        'operate_user',
    ];

    public function init()
    {
        return $this->hasOne('App\Models\ScriptInit', 'id', 'script_init_id');
    }
}