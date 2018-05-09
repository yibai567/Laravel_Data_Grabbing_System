<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScriptModel extends Model
{
    use SoftDeletes;

    const DEFAULT_SYSTEM_TYPE = 1;

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

    protected $fillable = [
        'name',
        'description',
        'structure',
        'parameters',
        'languages_type',
        'system_type',
        'operate_user',
    ];
}