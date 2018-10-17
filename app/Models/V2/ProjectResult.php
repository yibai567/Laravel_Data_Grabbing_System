<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectResult extends Model
{
    use SoftDeletes;

    //语言类型 1、英文 2、中文
    const LANGUAGE_TYPE_ENGLISH = 1;
    const LANGUAGE_TYPE_CHINESE = 2;

    /**
     * 表名
     */
    protected $table = 't_project_result';

    /**
     * 主键
     */
    protected $primaryKey = "id";

    /**
     * 隐藏的字段
     */
    protected $hidden = [
        "deleted_at",
        "updated_at",
    ];

    /**
     *
     * 可更新的字段
     */
    protected $fillable = [
        'content_type',
        'company',
        'task_id',
        'project_id',
        'task_run_log_id',
        'title',
        'content',
        'description',
        'detail_url',
        'show_time',
        'author',
        'read_count',
        'thumbnail',
        'screenshot',
        'language_type',
        'status',
        'start_time',
        'end_time',
        'created_time',
    ];

    /**
     * 设置screenshot字段入库前转化为json
     *
     * @param  array  $value
     * @return string
     */
    public function setScreenshotAttribute($value)
    {
        $this->attributes['screenshot'] = json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 获取screenshot字段时转化为array
     *
     * @param  string  $value
     * @return array
     */
    public function getScreenshotAttribute($value)
    {
        return json_decode($value, true);
    }

    /**
     * 设置thumbnail字段入库前转化为json
     *
     * @param  array  $value
     * @return string
     */
    public function setThumbnailAttribute($value)
    {
        $this->attributes['thumbnail'] = json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 获取thumbnail字段时转化为array
     *
     * @param  string  $value
     * @return array
     */
    public function getThumbnailAttribute($value)
    {
        return json_decode($value, true);
    }
}