<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WechatServerLog extends Model
{
    use SoftDeletes;

    /**
     * 表名
     */
    protected $table = 't_wechat_server_log';

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
        'wechat_server_id',
        'content',
    ];
}
