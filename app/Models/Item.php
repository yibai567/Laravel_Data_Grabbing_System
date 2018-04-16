<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use SoftDeletes;

    //初始化状态
    const STATUS_INIT = 1; // 初始化
    const STATUS_TESTING = 2; // 测试中
    const STATUS_TEST_SUCCESS = 3; // 测试成功
    const STATUS_TEST_FAIL = 4; // 测试失败
    const STATUS_START = 5; // 启动
    const STATUS_STOP = 6; // 停止

    const TYPE_OUT = 1; // 外部调用
    const TYPE_SYS = 2; // 系统

    const IS_CAPTURE_IMAGE_TRUE = 1; // 需要截图
    const IS_CAPTURE_IMAGE_FALSE = 2; // 不需要截图

    const DATA_TYPE_HTML = 1; // 数据类型为html
    const DATA_TYPE_JSON = 2; // 数据类型为json
    const DATA_TYPE_CAPTURE = 3; // 数据类型为截图

    const IS_PROXY_YES = 1; // 需要代理
    const IS_PROXY_NO = 2; // 不需要代理

    protected $dates = ['deleted_at'];

    /**
     * 表名
     */
    protected $table = 't_item';

    /**
     * 可更新的字段
     */
    protected $fillable = [
        'id',
        'name',
        'data_type',
        'content_type',
        'type',
        'action_type',
        'is_capture_image',
        'associate_result_id',
        'resource_url',
        'short_content_selector',
        'long_content_selector',
        'row_selector',
        'cron_type',
        'is_proxy',
        'last_job_at',
        'status',
    ];

    /**
     * 隐藏的字段
     */
    protected $hidden = [
        'deleted_at',
    ];
}
