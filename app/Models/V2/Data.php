<?php
namespace App\Models\V2;

use App\Models\BlockNews;
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
        'thumbnail',
        'img_task_undone',
        'img_task_total',
        'screenshot',
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