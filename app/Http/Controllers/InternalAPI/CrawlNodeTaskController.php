<?php

namespace App\Http\Controllers\InternalAPI;

use App\Models\CrawlNodeTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CrawlNodeTaskController extends Controller
{
    /**
     * 创建节点任务
     * @param Request $request
     * @return array
     */
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

        $dispatcher = app('Dingo\Api\Dispatcher');
        $res = $dispatcher->get('internal_api/basic/crawl/node/get_usable_node');
        if ($res['status_code'] === 401) {
            return response(['message' => '没有可用Node节点'], 401);
        }
        $node = $res['data'];
        if (empty($node) && empty($node['id'])) {
            return response('没有可用的Node节点', 401);
        }

        $data = [
            'node_id' => $node['id'],
            'crawl_task_id' => $request->crawl_task_id,
            'cmd_startup' => '',
            'cmd_stop' => '',
            'log_path' => '',
            'status' => CrawlNodeTask::IS_STARTUP,
            'start_on' => null,
            'end_on' => null,
        ];

        $dispatcher = app('Dingo\Api\Dispatcher');
        $res = $dispatcher->post('internal_api/basic/crawl/node_task', $data);
        $data = [];
        if ($res['data']) {
            $data = $res['data'];
        }
        return $this->resObjectGet($data, 'node_task.create', $request->path());
    }
}
