<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WechatServer extends Model
{
    use SoftDeletes;

    const STATUS_INIT = 1;
    const STATUS_START = 2;
    const STATUS_STOP = 3;

    const LISTEN_TYPE_ROOM = 1;
    const LISTEN_TYPE_OFFICIAL = 2;

    /**
     * 表名
     */
    protected $table = 't_wechat_server';

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

    protected $fillable = [
        'wechat_name',
        'room_name',
        'listen_type',
        'status',
    ];
}
