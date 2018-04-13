<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use SoftDeletes;

    //初始化状态
    const STATUS_INIT = 1;
    const STATUS_SUCCESS = 2;
    const STATUS_TEST_NO_PROXY_FAIL = 3;
    const STATUS_TEST_PROXY_FAIL = 4;
    const STATUS_TEST_FAIL = 5;
    const STATUS_START = 6;
    const STATUS_STOP = 7;

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
