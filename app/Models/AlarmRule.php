<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AlarmRule extends Model
{
    use SoftDeletes;

    /**
     * 表名
     */
    protected $table = 't_alarm_rule';

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
        'id',
        'name',
        'description',
        'expression',
        'expression_value',
        'receive_email',
        'receive_phone',
        'receive_wework',
        'status',
    ];
}
