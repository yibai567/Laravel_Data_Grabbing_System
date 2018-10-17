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
    const DATA_TYPE_CASPERJS = 1;
    const DATA_TYPE_HTML = 2;
    const DATA_TYPE_API = 3;

    //任务执行类型 1、每分钟 2、每五分钟 3、每十分钟
    const CRON_TYPE_KEEP = 1;
    const CRON_TYPE_EVERY_FIVE_MINUTES = 2;
    const CRON_TYPE_EVERY_TEN_MINUTES = 3;
    const CRON_TYPE_ONCE = 4;

    //下载图片 1、是 2、否
    const DOWNLOAD_IMAGE_TRUE = 1;
    const DOWNLOAD_IMAGE_FALSE = 2;

    //上报数据 1、是 2、否
    const REPORT_DATA_TRUE = 1;
    const REPORT_DATA_FALSE = 2;

    //是否翻墙 1、是 2、否
    const WALL_OVER_TRUE = 1;
    const WALL_OVER_FALSE = 2;

    //生成模式 1、模块模式 2、文件模式
    const GENERATE_TYPE_MODULE = 1;
    const GENERATE_TYPE_CONTENT = 2;

    //脚本类型 1、JS类型 2、PHP类型
    const SCRIPT_TYPE_JS = 1;
    const SCRIPT_TYPE_PHP = 2;

    //语言类型 1、英文 2、中文
    const LANGUAGE_TYPE_ENGLISH = 1;
    const LANGUAGE_TYPE_CHINESE = 2;

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
        'list_url',
        'script_init_id',
        'step',
        'last_generate_at',
        'languages_type',
        'status',
        'operate_user',
        'next_script_id',
        'requirement_pool_id',
        'language_type',
        'is_report',
        'is_download',
        'is_proxy',
        'generate_at',
        'script_type',
    ];

    public function config()
    {
        return $this->hasOne('App\Models\ScriptConfig', 'id', 'script_init_id');
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