<?php

namespace App\Services;

use Log;

class APIService extends Service
{
    public static function internalPost($url, $params = []) {
        $url = config('url.jinse_internal_url') . $url;
        $dispatcher = app('Dingo\Api\Dispatcher');
        return $dispatcher->post($url, $params);
    }
}
