<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskFilterMap extends Model
{
    use SoftDeletes;

    /**
     * 表名
     */
    protected $table = 't_task_filter_map';

    /**
     *
     * 可更新的字段
     */
    protected $fillable = [
        'task_id',
        'project_id',
        'filter_id',
        'params',
    ];

    /**
     * 隐藏的字段
     */
    protected $hidden = [
        'deleted_at',
    ];

    /**
     * 设置params字段入库前转化为json
     *
     * @param  array  $value
     * @return string
     */
    public function setParamsAttribute($value)
    {
        $this->attributes['params'] = json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 获取params字段时转化为array
     *
     * @param  string  $value
     * @return array
     */
    public function getParamsAttribute($value)
    {
        return json_decode($value, true);
    }

}