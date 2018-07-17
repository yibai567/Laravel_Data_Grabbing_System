<?php

namespace App\Models\V2;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WechatCoinNews extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    /**
     * 表名
     */
    protected $table = 't_wechat_coin_news';

    /**
     * 可更新的字段
     */
    protected $fillable = [
        'coin_name',
        'title',
        'detail_url',
        'description',
        'wechat_subscription_number',
        'publish_time',
        'md5_all'
    ];

    /**
     * 隐藏的字段
     */
    protected $hidden = [
        'deleted_at',
    ];
}