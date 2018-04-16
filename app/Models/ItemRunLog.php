<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemRunLog extends Model
{
    use SoftDeletes;

    const TYPE_TEST = 1;
    const TYPE_PRO = 2;

    protected $dates = ['deleted_at'];
    protected $table = 't_item_run_log';

    protected $fillable = [
        'item_id',
        'type',
        'start_at',
        'end_at',
        'status',
    ];

    protected $hidden = [
        'deleted_at',
    ];
}
