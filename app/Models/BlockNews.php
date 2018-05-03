<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlockNews extends Model
{
    use SoftDeletes;

    // 正常状态
    const STATUS_NORMAL = 1;
    // 已删除
    const STATUS_DELETE = 2;
    /**
     * 表名
     */
    protected $table = 't_block_news';

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

    public $fillable = [
        'content',
        'title',
        'last_updated_at',
        'company',
        'category',
    ];
}
