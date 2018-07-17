<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AlarmResult extends Model
{
    use SoftDeletes;

    //数据状态
    const STATUS_SEND = 1; // 发送中
    const STATUS_SUCCESS = 2; // 成功
    const STATUS_FAIL = 3; // 失败

    //类型
    const TYPE_WEWORK = 1; // 企业微信
    const TYPE_PHONE = 2; // 手机

    protected $dates = ['deleted_at'];

    /**
     * 表名
     */
    protected $table = 't_alarm_result';

    /**
     * 可更新的字段
     */
    protected $fillable = [
        'type',
        'content',
        'phone',
        'wework',
        'send_at',
        'status',
    ];

    /**
     * 隐藏的字段
     */
    protected $hidden = [
        'deleted_at',
    ];
}