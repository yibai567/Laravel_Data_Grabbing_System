<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErrorMessage extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    /**
     * 表名
     */
    protected $table = 't_error_message';

    /**
     * 主键
     */
    protected $primaryKey = "id";

    /**
     *
     * 可更新的字段
     */
    protected $fillable = [
        'raw',
        'msg',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * 隐藏的字段
     */
    protected $hidden = [
        'deleted_at',
    ];
}
