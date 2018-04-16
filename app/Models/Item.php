<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use SoftDeletes;

    //初始化状态
    const STATUS_INIT = 1;
    const STATUS_TESTING = 2;
    const STATUS_TEST_SUCCESS = 3;
    const STATUS_TEST_NO_PROXY_FAIL = 4;
    const STATUS_TEST_PROXY_FAIL = 5;
    const STATUS_TEST_FAIL = 6;
    const STATUS_START = 7;
    const STATUS_STOP = 8;

    const IS_CAPTURE_IMAGE_TRUE = 1;
    const IS_CAPTURE_IMAGE_FALSE = 2;

    const TYPE_OUT = 1;
    const TYPE_SYS = 2;

    const DATA_TYPE_HTML = 1;
    const DATA_TYPE_JSON = 2;
    const DATA_TYPE_CAPTURE = 3;

    const IS_PROXY_YES = 1;
    const IS_PROXY_NO = 2;


    protected $dates = ['deleted_at'];
    protected $table = 't_item';

    protected $fillable = [
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

    protected $hidden = [
        'deleted_at',
    ];
}
