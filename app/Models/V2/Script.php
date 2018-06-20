<?php

namespace App\Models\V2;


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

    //后缀类型 1、JS类型 2、PHP类型
    const EXT_TYPE_JS = 1;
    const EXT_TYPE_PHP = 2;

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
        'requirement_pool_id',
        'company_id',
        'name',
        'description',
        'list_url',
        'data_type',
        'casper_config_id',
        'modules',
        'content',
        'last_generate_at',
        'is_proxy',
        'projects',
        'filters',
        'actions',
        'cron_type',
        'ext',
        'created_by',
        'status',
    ];

    public function casperConfig()
    {
        return $this->hasOne('App\Models\ScriptConfig', 'id', 'casper_config_id');
    }

    /**
     * 设置modules字段入库前转化为json
     *
     * @param  array  $value
     * @return string
     */
    public function setModulesAttribute($value)
    {
        $this->attributes['modules'] = json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 设置projects字段入库前转化为json
     *
     * @param  array  $value
     * @return string
     */
    public function setProjectsAttribute($value)
    {
        $this->attributes['projects'] = json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 设置filters字段入库前转化为json
     *
     * @param  array  $value
     * @return string
     */
    public function setFiltersAttribute($value)
    {
        $this->attributes['filters'] = json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 设置actions字段入库前转化为json
     *
     * @param  array  $value
     * @return string
     */
    public function setActionsAttribute($value)
    {
        $this->attributes['actions'] = json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 获取modules字段时转化为array
     *
     * @param  string  $value
     * @return array
     */
    public function getModulesAttribute($value)
    {
        return json_decode($value, true);
    }

    /**
     * 获取projects字段时转化为array
     *
     * @param  string  $value
     * @return array
     */
    public function getProjectsAttribute($value)
    {
        return json_decode($value, true);
    }

    /**
     * 获取filters字段时转化为array
     *
     * @param  string  $value
     * @return array
     */
    public function getFiltersAttribute($value)
    {
        return json_decode($value, true);
    }

    /**
     * 获取actions字段时转化为array
     *
     * @param  string  $value
     * @return array
     */
    public function getActionsAttribute($value)
    {
        return json_decode($value, true);
    }
}