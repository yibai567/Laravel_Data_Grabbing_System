<?php

namespace App\Models;

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
        'status',
        'updated_at',
        'operate_by',
        'create_by',
    ];


}
