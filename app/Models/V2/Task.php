<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes;

    //脚本类型
    const DATA_TYPE_CASPERJS = 1;
    const DATA_TYPE_HTML = 2;
    const DATA_TYPE_API = 3;

    //任务执行类型 1、每分钟 2、每五分钟 3、每十分钟
    const CRON_TYPE_KEEP = 1;
    const CRON_TYPE_EVERY_FIVE_MINUTES = 2;
    const CRON_TYPE_EVERY_TEN_MINUTES = 3;
    const CRON_TYPE_KEEP_ONCE = 4;

    //任务状态 1、初始化，2、启动，3停止

    const STATUS_INIT = 1;
    const STATUS_START = 2;
    const STATUS_STOP = 3;

    //任务测试状态
    const TEST_STATUS_SUCCESS = 1;
    const TEST_STATUS_FAIL = 2;

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
        'list_url',
        'data_type',
        'script_path',
        'is_proxy',
        'projects',
        'filters',
        'actions',
        'cron_type',
        'ext',
        'publisher',
        'status',
        'requirement_pool_id',
        'company_id',
    ];

    /**
     * 隐藏的字段
     */
    protected $hidden = [
        'deleted_at',
    ];

    public function taskStatistics()
    {
        return $this->hasOne('App\Models\V2\TaskLastRunLog', 'task_id', 'id');
    }

    public function script()
    {
        return $this->belongsTo('App\Models\Script', 'script_id', 'id');
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
     * 设置test_result字段入库时转化为json
     * @param $value
     */
    public function setTestResultAttribute($value)
    {
        $this->attributes['test_result'] = json_encode($value, JSON_UNESCAPED_UNICODE);
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

    /**
     * 获取test_result字段时转化成array
     *
     * @param string $value
     * @return string
     */
    public function getTestResultAttribute($value)
    {
        return json_decode($value, true);
    }
}
