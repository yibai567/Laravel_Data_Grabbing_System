<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemRunLog extends Model
{
    use SoftDeletes;

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
