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

    //协议类型 1、http 2、https
    const PROTOCOL_HTTP = 1;
    const PROTOCOL_HTTPS = 2;

    //是否需要代理 1、需要 2、不需要
    const IS_PROXY_TRUE = 1;
    const IS_PROXY_FALSE = 2;

    //是否ajax请求 1、是 2、否
    const IS_AJAX_TRUE = 1;
    const IS_AJAX_FALSE = 2;

    //是否被墙 1、是 2、否
    const IS_WALL_TRUE = 1;
    const IS_WALL_FALSE = 2;

    //是否需要登录 1、是 2、否
    const IS_LOGIN_TRUE = 1;
    const IS_LOGIN_FALSE = 2;

    //任务执行类型 1、保持 2、每分钟 3、每五分钟 4、每十分钟 5、每十五分钟 6、每二十分钟
    const CRON_TYPE_KEEP = 1;
    const CRON_TYPE_EVERY_MINUTE = 2;
    const CRON_TYPE_EVERY_FIVE_MINUTES = 3;
    const CRON_TYPE_EVERY_TEN_MINUTES = 4;
    const CRON_TYPE_EVERY_FIFTEEN_MINUTES = 5;
    const CRON_TYPE_EVERY_TWENTY_MINUTES = 6;

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
        'status',
        'protocol',
        'is_proxy',
        'is_ajax',
        'is_login',
        'is_wall',
        'start_time',
        'keywords',
        'last_job_at',
        'type',
        'header',
    ];

    public function setting()
    {
        return $this->hasOne('App\Models\CrawlSetting', 'id', 'setting_id');
    }
}
