<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HistoryTopic extends Model
{
    use SoftDeletes;
    /**
     * 表名
     */
    protected $table = 't_history_topic';

    //初始化状态
    const STATUS_INIT = 1;     //未抓取
    const STATUS_RUN = 2;     //正在抓取
    const STATUS_FINISH = 3; //抓取完成

    protected $fillable = [
        'category',
        'company',
        'company_id',
        'title',
        'content',
        'tags',
        'show_time',
        'author',
        'read_count',
        'comment_count',
        'detail_url',
        'md5_url',
        'create_time',
        'status',
    ];
    /**
     * 隐藏的字段
     */
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

}