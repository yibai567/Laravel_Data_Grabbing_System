<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Data extends Model
{
    use SoftDeletes;

    // 正常状态
    const STATUS_NORMAL = 1;

    const CONTENT_TYPE_BANNER = 1; //Banner
    // const CONTENT_TYPE_INDEX = 2; //头条/推荐
    const CONTENT_TYPE_LIVE = 3; //快讯
    const CONTENT_TYPES = array(
        BlockNews::CONTENT_TYPE_BANNER => '首页',
        // BlockNews::CONTENT_TYPE_INDEX => '头条/推荐',
        BlockNews::CONTENT_TYPE_LIVE => '快讯'
    );

    /**
     * 表名
     */
    protected $table = 't_data';

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
        'content_type',
        'company',
        'task_id',
        'task_run_log_id',
        'title',
        'md5_title',
        'md5_content',
        'content',
        'detail_url',
        'show_time',
        'author',
        'read_count',
        'status',
        'start_time',
        'end_time',
        'created_time',
    ];
}