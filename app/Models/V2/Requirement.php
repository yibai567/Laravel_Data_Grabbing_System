<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Requirement extends Model
{
    use SoftDeletes;

    /**
     * 是否下载图片
     */
    const IS_DOWNLOAD_TRUE = 1; //是
    const IS_DOWNLOAD_FALSE = 2; //否

    /**
     * 是否截图
     */
    const IS_CAPTURE_TRUE = 1; //是
    const IS_CAPTURE_FALSE = 2; //否

    /**
     * 订阅类型
     */
    const SUBSCRIPTION_TYPE_LIST= 1; //列表
    const SUBSCRIPTION_TYPE_DETAIL= 2; //详情

    /**
     * 状态
     */
    const STATUS_TRUE = 1; //未处理
    const STATUS_FALSE = 2; //已处理
    const STATUS_STASH = 3; //暂存

    /**
     * 任务执行类型 1、每分钟 2、每五分钟 3、每十分钟 4、只执行一次
     */
    const CRON_TYPE_KEEP = 1;
    const CRON_TYPE_EVERY_FIVE_MINUTES = 2;
    const CRON_TYPE_EVERY_TEN_MINUTES = 3;
    const CRON_TYPE_ONCE = 4;

    /**
     * 需求类型
     */
    const REQUIREMENT_TYPE_LIVE = 1;//快讯
    const REQUIREMENT_TYPE_NOTICE = 2; //公告

    /**
     * 分类
     */
    const CATEGORY_FAST_NEWS= 4; //行业快讯

    /**
     * 表名
     */
    protected $table = 't_requirement_pool';

    /**
     * 主键
     */
    protected $primaryKey = "id";

    protected $fillable = [
        'name',
        'list_url',
        'description',
        'subscription_type',
        'is_capture',
        'is_download_img',
        'img_description',
        'company_id',
        'status',
        'status_identity',
        'updated_at',
        'category',
        'operate_by',
        'create_by',
        'status_reason',
    ];


}
