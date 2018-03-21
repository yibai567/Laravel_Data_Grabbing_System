<?php

namespace App\Http\Controllers\InternalAPI;

use App\Events\TaskPreview;
use App\Http\Requests\CrawlTaskCreateRequest;
use App\Models\CrawlNode;
use App\Models\CrawlSetting;
use App\Models\CrawlTask;
use App\Services\APIService;
use App\Services\WeWorkService;
use Dompdf\Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

class CrawlTaskController extends Controller
{
    /**
     * 创建任务
     * @param Request $request
     * @return array
     */
    public function create(Request $request)
    {
        infoLog('[create] start.');
        $params = $request->all();
        infoLog('[create] validate.', $params);
        $validator = Validator::make($params, [
            'resource_url' => 'required|string|nullable',
            'cron_type' => 'integer|nullable',
            'keywords' => 'string|nullable',
            'setting_id' => 'integer',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            errorLog('[create] validate fail.', $errors);
            foreach ($errors->all() as $value) {
                errorLog('[create] validate fail message.', $value);
                return $this->resError(401, $value);
            }
        }

        infoLog('[create] validate end.');

        if (empty($params['cron_type'])) {
            $params['cron_type'] = 1;
        }
        if (empty($params['setting_id'])) {
            $params['setting_id'] = 1;
        }

        if (substr($params['resource_url'], 0, 8) == 'https://') {
            $params['protocol'] = CrawlTask::PROTOCOL_HTTPS;
        } else {
            $params['protocol'] = CrawlTask::PROTOCOL_HTTP;
        }
        if (empty($params['is_proxy'])) {
            $params['is_proxy'] = 2;
        }
        $data = $params;

        infoLog('[create] prepare data.', $data);
        try{
            infoLog('[create] prepare data.', $data);
            $res = APIService::basePost('/internal/basic/crawl/task', $data);
            infoLog('[create] create task.', $res);
            if ($res['status_code'] !== 200) {
                errorLog($res['message'], $res['status_code']);
                throw new Exception($res['message'], $res['status_code']);
            }
            $task = [];
            if ($res['data']) {
                $task = $res['data'];
            }
            $params = ['id'=> $task['id']];
            infoLog('[create] create success.', $task);

            // 异步触发TaskPreview事件
             event(new TaskPreview($task['id']));

            $result = $params;
        } catch (Exception $e){
            return $this->resError($e->getCode(), $e->getMessage());
        }
        return $this->resObjectGet($result, 'crawl_task', $request->path());
    }

    /**
     * 状态更新
     * @param Request $request
     * @return mixed
     */
    public function updateStatus(Request $request)
    {
        infoLog('[createScript] start.');
        $params = $request->all();
        $validator = Validator::make($params, [
            "id" => "integer|required",
            "status" => "integer|required"
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                return $this->resError(401, $value);
            }
        }
        $taskId = $params['id'];
        $status = $params['status'];
        $params = ['id' => $taskId, 'status' => $status];
        $data = APIService::internalPost('/internal/basic/crawl/task/status', $params);
        if ($data['status_code'] !== 200) {
            return $this->resError($data['status_code'], 'update fail');
        }
        $res = [];
        if ($data['data']) {
            $res = $data['data'];
        }

        return $this->resObjectGet($res, 'crawl_task.updateStatus', $request->path());
    }

    /**
     * 停止任务
     * @param Request $request
     */
    public function stop(Request $request)
    {
        infoLog('[stop] start.');
        $params = $request->all();
        infoLog('[stop] validate.', $params);
        $validator = Validator::make($params, [
            'id' => 'integer|required',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                infoLog('[stop] validate fail message.', $value);
                return $this->resError(401, $value);
            }
        }
        infoLog('[stop] validate end.');
        try {
            //获取任务详情
            $taskDetail = APIService::baseGet('/internal/basic/crawl/task?id=' . $params['id']);
            if ($taskDetail['status_code'] !== 200 || empty($taskDetail)) {
                errorLog($taskDetail['messsage']);
                throw new Exception($taskDetail['messsage'], $taskDetail['status_code']);
            }
            $taskData = $taskDetail['data'];
            if ($taskData['status'] !== CrawlTask::IS_START_UP) {
                return $this->resError(401, 'task not start!');
            }
            $params = ['id' => $taskData['id'], 'status' => CrawlTask::IS_PAUSE];
            $data = APIService::basePost('/internal/basic/crawl/task/status', $params);
            if ($data['status_code'] !== 200) {
                errorLog($data['messsage']);
                throw new Exception('[stop] ' . $data['status_code'] . 'stop fail', 401);
            }
        } catch (Exception $e) {
            return $this->resError($e->getCode(), $e->getMessage());
        }
        infoLog('[stop] end.');
        return $this->resObjectGet($taskData, 'crawl_task.stop', $request->path());
    }

    /**
     * 执行接口-测试使用
     * @return mixed
     */
    public function preview(Request $request)
    {
        infoLog('[preview] start.');
        $params = $request->all();
        infoLog('[preview] validate.', $params);
        $validator = Validator::make($params, [
            'id' => 'integer|required',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                infoLog('[preview] validate fail message.', $value);
                return $this->resError(401, $value);
            }
        }
        infoLog('[start] validate end.');
        try {
            // 获取任务详情
            $res = APIService::baseGet('/internal/basic/crawl/task?id=' . $params['id']);
            if ($res['status_code'] !== 200) {
                errorLog($res['messsage']);
                throw new Exception($res['messsage'], $res['status_code']);
            }

            $task = $res['data'];
            $scriptFile = config('path.jinse_script_path') . '/' . $task['script_file'];

            if (!file_exists($scriptFile)) {
                throw new Exception('[preview] script file not exist', 401);
            }
            $command = 'casperjs ' . $scriptFile . ' --env=test';
            exec($command, $output, $returnvar);
            $params = ['id' => $task['id']];
            if ($returnvar == 1) {
                $params['status'] = CrawlTask::IS_TEST_ERROR;
                $data = APIService::basePost('/internal/basic/crawl/task/status', $params);
                if ($data['status_code'] !== 200) {
                    throw new Exception('[preview] ' . $data['status_code'] . 'update fail', 401);
                }
                throw new Exception('[preview] command ' . $command . ' execute fail', 401);
            }
            $params['status'] = CrawlTask::IS_TEST_SUCCESS;
            $data = APIService::basePost('/internal/basic/crawl/task/status', $params);
            if ($data['status_code'] !== 200) {
                throw new Exception('[preview] ' . $data['status_code'] . 'update fail', 401);
            }
        } catch(Exception $e) {
            return $this->resError($e->getCode(), $e->getMessage());
        }

        return $this->resObjectGet($output, 'crawl_task.execute', $request->path());
    }

    /**
     * 启动任务
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function start(Request $request)
    {
        infoLog('[start] start.');
        $params = $request->all();
        infoLog('[start] validate.', $params);
        $validator = Validator::make($params, [
            'id' => 'integer|required',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                infoLog('[start] validate fail message.', $value);
                return $this->resError(401, $value);
            }
        }
        infoLog('[start] validate end.');

        try {
            //获取任务详情
            $taskDetail = APIService::baseGet('/internal/basic/crawl/task?id=' . $params['id']);
            if ($taskDetail['status_code'] !== 200 || empty($taskDetail)) {
                errorLog($taskDetail['messsage']);
                throw new Exception($taskDetail['messsage'], $taskDetail['status_code']);
            }
            $taskData = $taskDetail['data'];
            if ($taskData['status'] != CrawlTask::IS_TEST_SUCCESS && $taskData['status'] != CrawlTask::IS_PAUSE) {
                return $this->resError(401, 'task status does not allow to start');
            }
            // TODO 增加底层接口
            $currentTaskNumber = CrawlTask::where('status', CrawlTask::IS_START_UP)->count();
            $nodeNumber = CrawlNode::select('max_task_num')->first();
            $maxTaskNumber = $nodeNumber->max_task_num;
            if ($currentTaskNumber >= $maxTaskNumber) {
                $weWorkService = new WeWorkService();
                $noticeManager = config('wework.notice_manager');
                $content = '没有可用Node节点，请及时添加!';
                $weWorkService->sendTextToUser($noticeManager, $content);
            }
            $params = ['id' => $taskData['id'], 'status' => CrawlTask::IS_START_UP];
            $data = APIService::basePost('/internal/basic/crawl/task/status', $params);
            if ($data['status_code'] !== 200) {
                errorLog($data['messsage']);
                throw new Exception('[start] ' . $data['status_code'] . 'start fail', 401);
            }
        } catch (Exception $e) {
            return $this->resError($e->getCode(), $e->getMessage());
        }
        infoLog('[start] end.');
        return $this->resObjectGet($data, 'crawl_task.start', $request->path());
    }

    /**
     * 修改抓取返回结果
     */
    public function updateResult(Request $request)
    {
        infoLog('[updateResult] start.');
        $params = $request->all();
        infoLog('[updateResult] validate.', $params);
        $validator = Validator::make($params, [
            'id' => 'integer|required',
            'test_result' => 'nullable',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                infoLog('[start] validate fail message.', $value);
                return $this->resError(401, $value);
            }
        }
        infoLog('[updateResult] validate end.');
        $result = [];
        if (empty($params['test_result'])) {
            infoLog('[updateResult] test_result empty.');
            return $this->resObjectGet($result, 'crawl_task.result', $request->path());
        }
        infoLog('[updateResult] execute base api', $params);
        $resultData = APIService::basePost('/internal/basic/crawl/task/result', $params);
        if ($resultData['status_code'] !== 200) {
            errorLog('[updateResult] result task error.');
            return $this->resError($resultData['status_code'], $resultData['message']);
        }
        if ($resultData['data']) {
            $result = $resultData['data'];
        }
        infoLog('[updateResult] end.', $result);
        return $this->resObjectGet($result, 'crawl_task.result', $request->path());
    }

    /**
     * 任务列表
     * @param Request $request
     * @return 任务列表
     */
    public function all(Request $request)
    {
        infoLog('[all] start.');
        $params = $request->all();
        infoLog('[all] validate.', $params);
        $validator = Validator::make($params, [
            'limit' => 'integer|nullable',
            'offset' => 'integer|nullable',
            'protocol' => 'integer|nullable',
            'cron_type' => 'integer|nullable',
            'task_id' => 'integer|nullable',
            'is_proxy' => 'integer|nullable',
        ]);
        infoLog('[all] validate start.');
        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                infoLog('[start] validate fail message.', $value);
                return $this->resError(401, $value);
            }
        }
        if (empty($params['limit'])) {
            $params['limit'] = 10;
        }
        if (empty($params['offset'])) {
            $params['offset'] = 0;
        }
        if (empty($params['protocol'])) {
            $params['protocol'] = 1;
        }
        if (empty($params['cron_type'])) {
            $params['cron_type'] = 1;
        }
        if (empty($params['is_proxy'])) {
            $params['is_proxy'] = 2;
        }

        infoLog('[all] validate end.');
        $data = APIService::baseGet('/internal/basic/crawl/tasks', $params);
        if ($data['status_code'] !== 200) {
            errorLog('[all] result task error.');
            return $this->resError($data['status_code'], $data['message']);
        }

        if ($data['data']) {
            foreach ($data['data'] as $key => $value) {
                unset($value['test_result']);
                $result[] = $value;
            }
        }
        infoLog('[all] end.');
        return $this->resObjectGet($result, 'crawl_task.result', $request->path());
    }
        /**
     * 更新任务最后执行时间
     * @param Request $request
     */
    public function updateLastJobAt(Request $request)
    {
        infoLog('[updateLastJobAt] start.');
        $params = $request->all();
        infoLog('[updateLastJobAt] validate start.');
        $validator = Validator::make($params, [
            'id' => 'integer|required',
            'last_job_at' => 'required',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                infoLog('[updateLastJobAt] validate fail message.', $value);
                return $this->resError(401, $value);
            }
        }
        infoLog('[updateLastJobAt] validate end.');
        $data = APIService::basePost('/internal/basic/crawl/task/last_job_at', $params);
        if ($data['status_code'] !== 200) {
            errorLog('[updateLastJobAt] error.', $data);
            return $this->resError($data['status_code'], $data['message']);
        }

        if ($data['data']) {
            $result = $data['data'];
        }
        return $this->resObjectGet($result, 'crawl_task.updateLastJobAt', $request->path());
    }

    /**
     * 根据队列名获取队列数据
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByQueueName(Request $request)
    {
        infoLog('[getByQueueName] start.');
        $params = $request->all();
        infoLog('[getByQueueName] validate start.');
        $validator = Validator::make($params, [
            'name' => 'string|required|max:100',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                infoLog('[getByQueueName] validate fail message.', $value);
                return $this->resError(401, $value);
            }
        }
        infoLog('[getByQueueName] validate end.');
        try {
            Redis::connection('queue');
            $data = [];
            if (Redis::lLen($params['name']) > 0 ) {
                for ($i = 0; $i < 5; $i++) {
                    $value = Redis::rpop($params['name']);
                    if (is_null($value)) {
                        break;
                    }
                    $data[$i] = $value;
                }
            }
        } catch (Exception $e) {
            return $this->resError($e->getCode(), $e->getMessage());
        }
        return $this->resObjectGet($data, 'list', $request->path());
    }
}
