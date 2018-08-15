<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FastNews extends Model
{
    use SoftDeletes;

    // 正常状态
    const STATUS_NORMAL = 1;

    const CONTENT_TYPE_BANNER = 1; //Banner


    /**
     * 表名
     */
    protected $table = 't_fast_news';

    /**
     * 主键
     */
    protected $primaryKey = "id";

    /**
     * 隐藏的字段
     */
    protected $hidden = [
        "deleted_at",
    ];

    /**
     *
     * 可更新的字段
     */
    protected $fillable = [
        'requirement_id',
        'list_url',
        'title',
        'description',
        'content',
        'show_time'
    ];


}
