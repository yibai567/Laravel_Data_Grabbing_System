<?php

namespace App\Http\Controllers\InternalAPI\Basic;

use App\Http\Requests\CrawlTaskCreateRequest;
use App\Models\CrawlTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CrawlTaskController extends Controller
{
    /**
     * 创建爬虫任务接口-基础接口
     * @param CrawlTaskCreateRequest $request
     * @return json
     */
    public function create(CrawlTaskCreateRequest $request)
    {
        $data = $request->postFillData();
        if (empty($data)) {
            return response('返回数据为空', 404);
        }
        $task = CrawlTask::create($data);
        return $this->resObjectGet($task, 'crawl_task', $request->path());
    }

    /**
     * 更新任务状态接口
     * @param $taskId
     * @param int $status
     * @return json
     */
    public function updateStatus(Request $request)
    {
        $taskId = intval($request->get('task_id'));
        $status = intval($request->get('status'));

        $task = CrawlTask::findOrFail($taskId);
        if (empty($task)) {
            return response('任务不存在', 404);
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
            return response('status 参数错误', 401);
        }

        if ($status == $task->status) {
            return response('status 未做更改', 401);
        }

        $task->status = $status;
        $task->save();
        Log::info('task ' . $task->id . '状态更新成功, 当前状态为 ' . $task->status);
        return $this->resObjectGet($task, 'crawl_task', $request->path());
    }
}
