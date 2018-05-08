<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScriptInit extends Model
{
    use SoftDeletes;

    //执行规则
    const DEFAULT_CRON_TYPE = 1;

    //默认值
    const DEFAULT_LOAD_IMAGES = 2;
    const DEFAULT_LOAD_PLUGINS = 2;
    const DEFAULT_VERBOSE = 2;
    const DEFAULT_LOG_LEVEL = 'debug';

    /**
     * 表名
     */
    protected $table = 't_script_init';

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

    protected $fillable = [
        'load_images',
        'load_plugins',
        'log_level',
        'verbose',
        'width',
        'height',
    ];

    public function script()
    {
        return $this->belongsTo('App\Models\Script', 'script_init_id', 'id');
    }
}