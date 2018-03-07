<?php

namespace App\Http\Controllers\InternalAPI;

use App\Http\Requests\CrawlTaskCreateRequest;
use App\Models\CrawlTask;
use Illuminate\Http\Request;

class CrawlTaskController extends Controller
{
    public function create(CrawlTaskCreateRequest $request)
    {
        $params = $request->postFillData();
        $dispatcher = app('Dingo\Api\Dispatcher');
        $data = $dispatcher->post('internal_api/basic/crawl/task', $params);
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

        if ($status === CrawlTask::IS_INIT) {
            // TODO 生成文件
        }

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
        $data = $dispatcher->post('internal_api/basic/crawl/task', $params);
        if ($data['status_code'] == 200) {
            return $data['data'];
        } else {
            return $data;
        }
    }

    /**
     * 生成抓取任务脚本文件
     * @param Request $request
     */
    public function generateScript(Request $request)
    {
        $taskId = $request->get('task_id', null);
        $scriptContent = $request->get('content');
        if (!$taskId) {
            return response('参数错误', 401);
        }
        $task = CrawlTask::findOrFail($taskId);
        $now = time();

        $scriptFile = storage_path('scripts') . '/' . CrawlTask::SCRIPT_PREFIX . '_' . $task->id . '-' . $now . '.js';
        $task->script_generate_time = $now;
        $task->save();
        generateScript($scriptFile, $scriptContent);
    }

    /**
     * 执行接口
     * @return mixed
     */
    public function execute(Request $request)
    {
        $scriptFile = CrawlTask::SCRIPT_PATH . '/' . $request->get('filename');
        if (!file_exists($scriptFile)) {
            return response('脚本文件不存在', 401);
        }
        $command = 'casperjs ' . $scriptFile;
        exec($command, $output, $return_var);
        return $output;
    }
}
