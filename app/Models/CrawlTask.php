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

    const SCRIPT_PREFIX = 'grab';
    const SCRIPT_PATH = '/alidata/www/currency_dev/create_spider_file';
    protected $table = 't_crawl_task';

    protected $fillable = [
        'name',
        'description',
        'resource_url',
        'cron_type',
        'selectors',
        'status',
        'response_type',
        'response_url',
        'response_params',
        'setting_id',
        'test_result',
        'test_time',
        'script_generate_time'
    ];

    public function setting()
    {
        $this->hasOne('App\Models\CrawlSetting', 'setting_id', 'id');
    }
}
