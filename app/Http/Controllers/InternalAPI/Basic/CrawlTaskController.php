<?php

namespace App\Http\Controllers\InternalAPI\Basic;

use App\Http\Requests\CrawlTaskCreateRequest;
use App\Models\CrawlTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\InternalAPI\Controller;
use Illuminate\Support\Facades\Validator;

class CrawlTaskController extends Controller
{
    /**
     * 创建爬虫任务接口-基础接口
     * @param CrawlTaskCreateRequest $request
     * @return json
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string|nullable',
            'description' => 'string|nullable',
            'resource_url' => 'required|string|nullable',
            'cron_type' => 'integer|nullable',
            'selectors' => 'string|nullable',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                return  response($value, 401);
            }
        }
        $data = [
            'name' => $request->name,
            'description' => $request->description,
            'resource_url' => $request->resource_url,
            'cron_type' => intval($request->cron_type),
            'selectors' => $request->selectors,
            'status' => CrawlTask::IS_INIT,
            'response_type' => CrawlTask::RESPONSE_TYPE_API,
            'response_url' => '',
            'response_params' => '',
            'test_time' => null,
            'test_result' => '',
        ];
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
        $validator = Validator::make($request->all(), [
            "id" => "integer|required",
            "status" => "integer|required"
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                return  $this->response->error($value, 401);
            }
        }
        $taskId = intval($request->get('id'));
        $status = intval($request->get('status'));
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

        $task = CrawlTask::find($taskId);
        $data = [];
        if (empty($task)) {
            return  $this->response->error('任务不存在', 401);
        }
        $data = $task->toArray();
        if ($task->status !== $status) {
            $task->status = $status;
            $task->save();
        }

        return $this->resObjectGet($data, 'crawl_task', $request->path());
    }

    /**
     * 获取任务详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function retrieve(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id" => "nullable|integer|min:1",
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                return  response($value, 401);
            }
        }

        $taskId = intval($request->get('id'));
        $task = CrawlTask::with('setting')->find($taskId);
        $data = [];
        if ($task) {
            $data = $task->toArray();
        }
        return $this->resObjectGet($data, 'crawl_task', $request->path());
    }

    /**
     * 更新脚本最后更新时间
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateScriptLastGenerateTime(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id" => "nullable|integer",
            "script_last_generate_time" => "nullable|integer"
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                return  $this->response->error($value, 401);
            }
        }

        $taskId = intval($request->get('id'));
        $scriptLastGenerateTime = intval($request->get('script_last_generate_time'));
        if (empty($scriptLastGenerateTime)) {
            $scriptLastGenerateTime = time();
        }
        $task = CrawlTask::findOrFail($taskId);
        $task->script_last_generate_time = $scriptLastGenerateTime;
        $task->save();
        $data = $task->toArray();
        return $this->resObjectGet($data, 'crawl_task', $request->path());
    }
}
