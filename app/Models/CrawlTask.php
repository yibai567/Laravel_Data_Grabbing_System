<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrawlTask extends Model
{
    const IS_INIT = 1; //未启动
    const IS_TEST_SUCCESS = 2; //测试成功
    const IS_TEST_ERROR = 3; //测试失败
    const IS_START_UP = 4; //启动
    const IS_PAUSE = 5; //已停止
    const IS_ARCHIEVE = 6; //归档

    const RESPONSE_TYPE_API = 1; //API响应
    const RESPONSE_TYPE_EMAIL = 2; //邮件响应
    const RESPONSE_TYPE_SMS = 3; //短信响应
    const RESPONSE_TYPE_WEWORK = 4; //企业微信响应

    const PROTOCOL_HTTP = 1;
    const PROTOCOL_HTTPS = 2;

    const SCRIPT_PREFIX = 'grab';

    protected $table = 't_crawl_task';

    protected $fillable = [
        'name',
        'description',
        'resource_url',
        'cron_type',
        'selectors',
        'response_type',
        'response_url',
        'response_params',
        'setting_id',
        'test_result',
        'test_time',
        'script_file',
        'last_script_file',
        'status',
        'protocol',
        'is_proxy',
        'start_time',
        'last_job_at',
    ];

    public function setting()
    {
        return $this->hasOne('App\Models\CrawlSetting', 'id', 'setting_id');
    }
}
