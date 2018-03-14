<?php

namespace App\Http\Controllers\InternalAPI;

use App\Events\TaskPreview;
use App\Http\Requests\CrawlTaskCreateRequest;
use App\Models\CrawlSetting;
use App\Models\CrawlTask;
use App\Services\APIService;
use Dompdf\Exception;
use Illuminate\Http\Request;
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
//            $params = ['id'=> $task['id']];
//            infoLog('[create] prepare data.', $params);
//            $res = APIService::internalPost('/internal/crawl/task/script', $params);
//            infoLog('[create] create script.', $res);
//            if ($res['status_code'] !== 200) {
//                errorLog($res['message'], $res['status_code']);
//                throw new Exception($res['message'], $res['status_code']);
//            }
            // 异步触发TaskPreview事件
             event(new TaskPreview($task['id']));
//            $res = APIService::internalPost('/internal/crawl/task/preview', $params);
//            infoLog('[create] test script.', $res);
//            if ($res['status_code'] !== 200) {
//                errorLog($res['message'], $res['status_code']);
//                throw new Exception($res['message'], $res['status_code']);
//            }

//            $params = ['id'=> $task['id']];
//            infoLog('[create] prepare data.', $data);
//            $res = APIService::internalPost('/internal/crawl/task/start', $params);
//            infoLog('[create] start script.', $res);
//            if ($res['status_code'] !== 200) {
//                errorLog($res['message'], $res['status_code']);
//                throw new Exception($res['message'], $res['status_code']);
//            }
//            $params = ['id'=> $task['id'], 'status' => CrawlTask::IS_START_UP];
//            infoLog('[create] prepare data.', $data);
//            $res = APIService::internalPost('/internal/crawl/task/status', $params);
//            infoLog('[create] start script.', $res);
//            if ($res['status_code'] !== 200) {
//                errorLog($res['message'], $res['status_code']);
//                throw new Exception($res['message'], $res['status_code']);
//            }
            $result = $params;
        }catch(Exception $e){
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
        if ($status == CrawlTask::IS_INIT) {
            // 生成脚本
            $params = ['id' => $taskId];
            $res = APIService::internalPost('/internal/crawl/task/script', $params);
        }
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
            if ($taskData['status'] != CrawlTask::IS_START_UP) {
                return $this->resError(401, '当前状态无法启动');
            }
            $params['status'] = CrawlTask::IS_PAUSE;
            $data = APIService::basePost('/internal/basic/crawl/task/status', $params);
            if ($data['status_code'] !== 200) {
                errorLog($res['messsage']);
                throw new Exception('[stop] ' . $data['status_code'] . 'stop fail', 401);
            }
        } catch (Exception $e) {
            return $this->resError($res['status_code'], $res['message']);
        }
        infoLog('[stop] end.');
        return $this->resObjectGet($data, 'crawl_task.stop', $request->path());
    }

    // public function stop(Request $request)
    // {
    //     $params = $request->all();
    //     $validator = Validator::make($params, [
    //         'id' => 'integer|required',
    //     ]);

    //     if ($validator->fails()) {
    //         $errors = $validator->errors();
    //         foreach ($errors->all() as $value) {
    //             return $this->resError(401, $value);
    //         }
    //     }
    //     $taskId = $params['id'];
    //     $params = ['crawl_task_id' => $taskId];
    //     //停止指定任务id当前在运行的任务
    //     $res = APIService::baseGet('/internal/basic/crawl/node_task/started?crawl_task_id=' . $params['crawl_task_id']);

    //     if ($res['data']) {
    //         $item = $res['data'];
    //         $params = ['id' => $item['id']];
    //         $res = APIService::internalPost('/internal/crawl/node_task/stop', $params);
    //         if ($res['status_code'] !== 200) {
    //             errorLog($res['messsage']);
    //             throw new Exception($res['messsage'], $res['status_code']);
    //         }
    //     }
    //     // 更新任务状态为停止
    //     $params = ['id' => $taskId, 'status' => CrawlTask::IS_PAUSE];
    //     $res = APIService::internalPost('/internal/crawl/task/status', $params);
    //     if ($res['status_code'] !== 200) {
    //         errorLog($res['messsage']);
    //         throw new Exception($res['messsage'], $res['status_code']);
    //     }

    //     return $this->resObjectGet('task stop success', 'crawl_task.execute', $request->path());
    // }


    /**
     * 生成抓取任务脚本文件
     * @param Request $request
     */
    public function createScript(Request $request)
    {
        infoLog('[createScript] start.');
        $params = $request->all();
        infoLog('[createScript] validate.', $params);
        $validator = Validator::make($params, [
            'id' => 'integer|required',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $value) {
                infoLog('[createScript] validate fail message.', $value);
                return $this->resError(401, $value);
            }
        }
        infoLog('[createScript] validate end.');

        try {
            $data = [
                'id' => $params['id'],
            ];

            $res = APIService::basePost('/internal/basic/crawl/task/script', $data);

            if ($res['status_code'] !== 200) {
                errorLog($res['messsage']);
                throw new Exception($res['messsage'], $res['status_code']);
            }

            //调用基础接口获取任务详情
            $res = APIService::baseGet('/internal/basic/crawl/task?id=' . $params['id']);
            if ($res['status_code'] !== 200) {
                errorLog($res['messsage']);
                throw new Exception($res['messsage'], $res['status_code']);
            }
            $task = [];
            if ($res['data']) {
                $task = $res['data'];
            }

            if ($task['setting']['type'] == CrawlSetting::TYPE_CUSTOM) {
                //自定义模版类型
                $content = '';
            } else {
                $content = $task['setting']['content'];
                $content = str_replace('{{{crawl_task_id}}}', $task['id'], $content);
                $content = str_replace('{{{setting_selectors}}}', $task['selectors'], $content);
                $content = str_replace('{{{setting_keywords}}}', $task['keywords'], $content);
                $content = str_replace('{{{setting_data_type}}}', $task['setting']['data_type'], $content);
            }

            $filepath = config('path.jinse_script_path') . '/' . $task['script_file'];
            if (!generateScript($filepath, $content)) {
                return $this->resError(402, 'script generate fail');
            }
        } catch(Exception $e) {
            return $this->resError($res['status_code'], $res['message']);
        }
        return $this->resObjectGet('script generate success', 'crawl_task.generate_script', $request->path());
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
     * @return array
     */
    // public function start(Request $request)
    // {
    //     infoLog('[start] start.');
    //     $params = $request->all();
    //     infoLog('[start] validate.', $params);
    //     $validator = Validator::make($params, [
    //         'id' => 'integer|required',
    //     ]);

    //     if ($validator->fails()) {
    //         $errors = $validator->errors();
    //         foreach ($errors->all() as $value) {
    //             infoLog('[start] validate fail message.', $value);
    //             return $this->resError(401, $value);
    //         }
    //     }
    //     infoLog('[start] validate end.');

    //     try {
    //         // 获取任务详情
    //         $res = APIService::baseGet('/internal/basic/crawl/task?id=' . $params['id']);
    //         if ($res['status_code'] !== 200) {
    //             errorLog($res['messsage']);
    //             throw new Exception($res['messsage'], $res['status_code']);
    //         }
    //         $task = $res['data'];
    //         //创建节点任务
    //         $params = ['crawl_task_id' => $task['id']];

    //         $res = APIService::internalPost('/internal/crawl/node_task', $params);
    //         if ($res['status_code'] !== 200) {
    //             errorLog($res['messsage']);
    //             throw new Exception($res['messsage'], $res['status_code']);
    //         }

    //         $params = ['id' => $task['id'], 'status' => CrawlTask::IS_START_UP];
    //         $data = APIService::basePost('/internal/basic/crawl/task/status', $params);
    //         if ($data['status_code'] !== 200) {
    //             errorLog($res['messsage']);
    //             throw new Exception('[preview] ' . $data['status_code'] . 'start fail', 401);
    //         }
    //     } catch (Exception $e) {
    //         return $this->resError($res['status_code'], $res['message']);
    //     }
    //     infoLog('[start] end.');
    //     return $this->resObjectGet($task, 'crawl_task.execute', $request->path());
    // }
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
                return $this->resError(401, '当前状态无法启动');
            }
            $params['status'] = CrawlTask::IS_START_UP;
            $data = APIService::basePost('/internal/basic/crawl/task/status', $params);
            if ($data['status_code'] !== 200) {
                errorLog($res['messsage']);
                throw new Exception('[start] ' . $data['status_code'] . 'start fail', 401);
            }
        } catch (Exception $e) {
            return $this->resError($res['status_code'], $res['message']);
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
        ]);
        infoLog('[all] validate.', $validator);
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
}
