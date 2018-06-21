<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    /**
     * Exchange 类型
     **/
    const EXCHANGE_TYPE_DIRECT = 1;
    const EXCHANGE_TYPE_FANOUT = 2;
    const EXCHANGE_TYPE_ROUTE = 3;

    /**
     * 表名
     */
    protected $table = 't_project';

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
}