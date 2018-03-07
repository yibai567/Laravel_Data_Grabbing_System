<?php

namespace App\Http\Controllers\InternalAPI;

use App\Http\Requests\CrawlResultCreateRequest;

class CrawlResultController extends BaseController
{
    public function create(CrawlResultCreateRequest $request)
    {
        $params = $request->postFillData();
        $dispatcher = app('Dingo\Api\Dispatcher');
        $data = $dispatcher->post('basic/crawl/result', $params);
        if ($data['status_code'] == 401) {
            return response('参数错误', 401);
        }
        $result = [];
        if ($data['data']) {
            $result = $data['data'];
        }
        return $result;
    }
}
