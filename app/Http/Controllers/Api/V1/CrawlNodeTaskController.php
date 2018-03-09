<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CrawlNodeTaskController extends Controller
{
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'crawl_task_id' => 'integer|nullable',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                return response($value, 401);
            }
        }
        $data = [
            'crawl_task_id' => $request->crawl_task_id,
        ];
        $dispatcher = app('Dingo\Api\Dispatcher');
        $res = $dispatcher->post('internal_api/crawl/node_task', $data);
        $data = [];
        if ($res['data']) {
            $data = $res['data'];
        }
        return $this->resObjectGet($data, 'node_task.create', $request->path());
    }
}
