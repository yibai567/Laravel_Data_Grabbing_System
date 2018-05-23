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

    //脚本类型
    const LANGUAGES_TYPE_CASPERJS = 1;
    const LANGUAGES_TYPE_HTML = 2;
    const LANGUAGES_TYPE_API = 3;

    //任务执行类型 1、每分钟 2、每五分钟 3、每十分钟
    const CRON_TYPE_KEEP = 1;
    const CRON_TYPE_EVERY_FIVE_MINUTES = 2;
    const CRON_TYPE_EVERY_TEN_MINUTES = 3;

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

    /**
     *
     * 可更新的字段
     */
    protected $fillable = [
        'script_init_id',
        'step',
        'last_generate_at',
        'languages_type',
        'status',
        'operate_user',
    ];

    public function init()
    {
        return $this->hasOne('App\Models\ScriptInit', 'id', 'script_init_id');
    }

    /**
     * 设置step字段入库前转化为json
     *
     * @param  array  $value
     * @return string
     */
    public function setStepAttribute($value)
    {
        $this->attributes['step'] = json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 获取step字段时转化为array
     *
     * @param  string  $value
     * @return array
     */
    public function getStepAttribute($value)
    {
        return json_decode($value, true);
    }
}