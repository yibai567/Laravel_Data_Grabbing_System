<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Base\BasicController;
use App\Http\Requests\CrawlTaskCreateRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CrawlTaskController extends BasicController
{
    public function create(CrawlTaskCreateRequest $request)
    {
        $params = $request->postFillData();
        $dispatcher = app('Dingo\Api\Dispatcher');
        $data = $dispatcher->post('basic/crawl/task', $params);
        if ($data['status_code'] == 401) {
            return response('参数错误', 401);
        }
        $result = [];
        if ($data['data']) {
            $result = $data['data'];
        }
        return $result;
    }

    public function updateStatus(Request $request)
    {
        $taskId = intval($request->get('task_id'));
        $status = intval($request->get('status'));

        $task = CrawlTask::findOrFail($taskId);
        if (empty($task)) {
            return $this->response->error('任务不存在', 404);
        }
        $statusArr = [
            CrawlTask::IS_INIT,
            CrawlTask::IS_TEST_SUCCESS,
            CrawlTask::IS_TEST_ERROR,
            CrawlTask::IS_START_UP,
            CrawlTask::IS_PAUSE,
            CrawlTask::IS_ARCHIEVE,
        ];
        if (!in_array($status, $statusArr)) {
            return $this->response->error('status 参数错误', 401);
        }

        if ($status == $task->status) {
            return $this->response->error('status 未做更改', 401);
        }
        $params = ['task_id' => $taskId, 'status' => $status];
        $dispatcher = app('Dingo\Api\Dispatcher');
        $data = $dispatcher->post('basic/crawl/task', $params);
        if ($data['status_code'] == 200) {
            return $data['data'];
        } else {
            return $data;
        }
    }
}
