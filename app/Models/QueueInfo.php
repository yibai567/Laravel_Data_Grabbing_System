<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QueueInfo extends Model
{
    use SoftDeletes;

    const TYPE_HTML = 1;
    const TYPE_JSON = 2;
    const TYPE_CAPTURE = 3;
    const TYPE_TEST = 4;

    const IS_CAPTURE_IMAGE_TRUE = 1;
    const IS_CAPTURE_IMAGE_FALSE = 2;

    const DATA_TYPE_HTML = 1;
    const DATA_TYPE_JSON = 2;
    protected $dates = ['deleted_at'];
    protected $table = 't_queue_info';

    protected $fillable = [
        'name',
        'description',
        'current_length',
        'db',
        'is_proxy',
        'data_type',
        'type',
        'is_capture_image',
        'status',
    ];

    protected $hidden = [
        'deleted_at',
    ];
}
