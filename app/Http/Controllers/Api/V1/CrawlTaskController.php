<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\CrawlTaskCreateRequest;
use Illuminate\Http\Request;

class CrawlTaskController extends Controller
{
    /**
     * 创建任务接口
     * @param CrawlTaskCreateRequest $request
     * @return array
     */
    public function create(CrawlTaskCreateRequest $request)
    {
        $params = $request->postFillData();
        $dispatcher = app('Dingo\Api\Dispatcher');
        $data = $dispatcher->post('internal_api/crawl/task', $params);
        if ($data['status_code'] == 401) {
            return response('参数错误', 401);
        }
        $result = [];
        if ($data['data']) {
            $result = $data['data'];
        }
        return $result;
    }

    /**
     * 更新状态接口
     * @param Request $request
     * @return mixed
     */
    public function updateStatus(Request $request)
    {
        $taskId = intval($request->get('task_id'));
        $status = intval($request->get('status'));
        $params = ['id' => $taskId, 'status' => $status];
        $dispatcher = app('Dingo\Api\Dispatcher');
        $data = $dispatcher->post('internal_api/crawl/task/status', $params);
        if ($data['status_code'] == 401) {
            return response('参数错误', 401);
        }
        $result = [];
        if ($data['data']) {
            $result = $data['data'];
        }
        return $result;
    }

    /**
     * 生成脚本接口
     * @param Request $request
     * @return array
     */
    public function generateScript(Request $request)
    {
        $taskId = intval($request->get('id'));
        $params = ['id' => $taskId];
        $dispatcher = app('Dingo\Api\Dispatcher');
        $data = $dispatcher->post('internal_api/crawl/task/generate_script', $params);
        if ($data['status_code'] == 401) {
            return response('参数错误', 401);
        }
        $result = [];
        if ($data['data']) {
            $result = $data['data'];
        }
        return $result;
    }

    /**
     * 执行脚本接口
     * @param Request $request
     * @return mixed
     */
    public function execute(Request $request)
    {
        $taskId = intval($request->get('id'));
        $params = ['id' => $taskId];
        $dispatcher = app('Dingo\Api\Dispatcher');
        $data = $dispatcher->post('internal_api/crawl/task/execute', $params);
        return $data;
    }
}
