<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScriptModel extends Model
{
    use SoftDeletes;

    //模块类型
    const SYSTEM_TYPE_DEFAULT = 1;
    const SYSTEM_TYPE_DEFINED = 2;
    const SYSTEM_TYPE_BASE = 3;

    //脚本类型
    const LANGUAGES_TYPE_CASPERJS = 1;
    const LANGUAGES_TYPE_HTML = 2;
    const LANGUAGES_TYPE_API = 3;

    /**
     * 表名
     */
    protected $table = 't_script_model';

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

    /**
     *
     * 可更新的字段
     */
    protected $fillable = [
        'name',
        'description',
        'structure',
        'parameters',
        'languages_type',
        'system_type',
        'operate_user',
        'sort'
    ];
}