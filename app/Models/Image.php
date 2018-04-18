<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Image extends Model
{
    use SoftDeletes;

    /**
     * 是否使用框架自动维护 created_at，updated_at 字段
     */
    public $timestamps = true;
    /**
     * 表名
     */
    protected $table = 't_image';
    /**
     * 隐藏的字段
     */
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
