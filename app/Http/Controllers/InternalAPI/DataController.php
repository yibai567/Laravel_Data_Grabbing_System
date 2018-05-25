<?php
/**
 * DataController
 * 数据控制器
 * @author liqi@jinse.com
 * @version 1.0
 * Date: 2018/05/11
 */

namespace App\Http\Controllers\InternalAPI;

use App\Events\DataResultReportEvent;
use App\Models\Data;
use App\Models\TaskRunLog;
use App\Services\InternalAPIService;
use Log;
use App\Services\ValidatorService;
use Illuminate\Http\Request;


class DataController extends Controller
{
    /**
     * batchCreate
     * 批量插入
     *
     * @param
     * @return boolean
     */
    public function batchCreate(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'company' => 'required|string|max:500',
            'content_type' => 'required|integer|between:1,9',
            'task_run_log_id' => 'nullable|integer',
            'start_time' => 'required|date',
            'end_time' => 'required|date',
            'result' => 'required|array'
        ]);

        //如果未传task_run_log_id,是为测试脚本,直接返回结果
        if (empty($params['task_run_log_id'])) {
            return response()->json(true);
        }

        $taskRunLog = TaskRunLog::find($params['task_run_log_id']);
        if (empty($taskRunLog)) {
            Log::debug('[DataController batchCreate]  $taskRunLog is not found,task_run_log_id : '.$params['task_run_log_id']);
        }
        //查询taskRunLog对应的taskId
        $taskId = $taskRunLog->task_id;

        $updateTaskStatisticsData['task_id'] = $taskId;
        //记录脚本运行记录
        $result = InternalAPIService::post('/task_statistics/update', $updateTaskStatisticsData);

        if (!$result) {
            Log::debug('[DataController batchCreate] update task statistics is failed,task_id : '.$taskId);

            $updateTaskRunLogData['id'] = $params['task_run_log_id'];
            //更改task_runRunLog状态
            InternalAPIService::post('/task_run_log/status/fail', $updateTaskRunLogData);

            return response()->json(false);
        }

        $newData = [];
        foreach ($params['result'] as $value) {
            if ($params['content_type'] == Data::CONTENT_TYPE_LIVE) {
                if (empty($value['content'])) {
                    Log::debug('[batchCreate] $value["content"] empty');
                    continue;
                }

            } else {
                if (empty($value['title'])) {
                    Log::debug('[batchCreate] $value["title"] value empty');
                    continue;
                }

            }

            //监测content内容和title,有则进行加密,便于后面查重
            if (!empty($value['content'])) {
                $value['content'] = trim($value['content']);
                $value['md5_content'] = md5($value['content']);
            }
            if (!empty($value['title'])) {
                $value['title'] = trim($value['title']);
                $value['md5_title'] = md5($value['title']);
            }

            if (empty($value['title'])) {
                $row = Data::where('md5_content', $value['md5_content'])->first();
            } else {
                $row = Data::where('md5_title', $value['md5_title'])->first();
            }

            //内容已存在,更新信息
            if (!empty($row)) {
                if (!empty($value['read_count']) && $row->read_count != $value['read_count']) {
                    $row->read_count = $value['read_count'];
                    $row->updated_at = date('Y-m-d H:i:s');
                    $row->save();
                }
                continue;
            }

            $newData[] = [
                'content_type' => $params['content_type'],
                'company' => $params['company'],
                'title' => $value['title'],
                'md5_title' => $value['md5_title'],
                'md5_content' => $value['md5_content'],
                'content' => $value['content'],
                'task_id' => $taskId,
                'task_run_log_id' => $params['task_run_log_id'],
                'detail_url' => $value['detail_url'],
                'show_time' => $value['show_time'],
                'author' => $value['author'],
                'read_count' => $value['read_count'],
                'status' => Data::STATUS_NORMAL,
                'start_time' => $params['start_time'],
                'end_time' => $params['end_time'],
                'created_time' => time(),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }

        try {
            if (!empty($newData)) {
                $result = Data::insert($newData);

                if ($result) {
                    //事件监听,处理上报数据
                    event(new DataResultReportEvent($newData));
                }
            }

            //修改task_run_log信息;
            $result_count = count($newData);
            $updateTaskRunLogData['result_count'] = $result_count;
            $updateTaskRunLogData['id'] = $params['task_run_log_id'];

            //更改task_runRunLog状态
            InternalAPIService::post('/task_run_log/status/success', $updateTaskRunLogData);

        } catch (\Exception $e) {
            Log::debug('[DataController batchCreate] error message = ' . $e->getMessage());

            $updateTaskRunLogData['id'] = $params['task_run_log_id'];
            //更改task_runRunLog状态
            InternalAPIService::post('/task_run_log/status/fail', $updateTaskRunLogData);

            return response()->json(false);
        }


        return response()->json(true);

    }

    /**
     * batchSave
     * 批量插入
     *
     * @param
     * @return boolean
     */
    public function batchSave(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'company'         => 'required|string|max:500',
            'content_type'    => 'required|integer|between:1,9',
            'task_run_log_id' => 'required|integer',
            'task_id'         => 'required|integer',
            'start_time'      => 'required|date',
            'end_time'        => 'required|date',
            'result'          => 'required|array'
        ]);

        $result = [];
        foreach ($params['result'] as $value) {

            ValidatorService::check($value, [
                'title'      => 'nullable|string|max:255',
                'content'    => 'nullable|string',
                'detail_url' => 'nullable|string',
                'show_time'  => 'nullable|string|max:100',
                'author'     => 'nullable|string|max:50',
                'read_count' => 'nullable|string|max:100',
                'images'  => 'nullable|string',
            ]);

            //监测content内容和title,有则进行加密,便于后面查重
            if (!empty($value['content'])) {
                $value['content'] = trim($value['content']);
                $value['md5_content'] = md5($value['content']);
            }
            if (!empty($value['title'])) {
                $value['title'] = trim($value['title']);
                $value['md5_title'] = md5($value['title']);
            }

            //检测数据库是否已存在数据
            if (empty($value['title'])) {
                $row = Data::where('md5_content', $value['md5_content'])->first();
            } else {
                $row = Data::where('md5_title', $value['md5_title'])->first();
            }

            //内容已存在,更新信息
            if (!empty($row)) {
                if (!empty($value['read_count']) && $row->read_count != $value['read_count']) {
                    $row->read_count = $value['read_count'];
                    $row->save();
                }
                continue;
            }

            //整理保存数据
            $data = [
                'content_type'       => $params['content_type'],
                'company'            => $params['company'],
                'title'              => $value['title'],
                'md5_title'          => $value['md5_title'],
                'md5_content'        => $value['md5_content'],
                'content'            => $value['content'],
                'task_id'            => $params['task_id'],
                'task_run_log_id'    => $params['task_run_log_id'],
                'detail_url'         => $value['detail_url'],
                'show_time'          => $value['show_time'],
                'author'             => $value['author'],
                'read_count'         => $value['read_count'],
                'thumbnail'          => $value['images'],
                'status'             => Data::STATUS_NORMAL,
                'start_time'         => $params['start_time'],
                'end_time'           => $params['end_time'],
                'created_time'       => time()
            ];

            $result[] = Data::create($data);
        }

        return $this->resObjectGet($result, 'data', $request->path());
    }

    /**
     * retrieve
     * 详情
     *
     * @param
     * @return array
     */
    public function retrieve(Request $request)
    {
        $params = $request->all();

        //检测参数
        ValidatorService::check($params, [
            'id' => 'required|integer',
        ]);
        $data = Data::find($params['id']);

        $result = [];

        if (!empty($data)) {
            $result = $data->toArray();
        }

        return $this->resObjectGet($result, 'task', $request->path());
    }
    /**
     * listByIds
     * 根据多个id查数据信息
     *
     * @param ids
     * @return array
     */
    public function listByIds(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'ids' => 'required|string|max:100',
        ]);

        $ids = explode(',', $params['ids']);
        $datas = Data::whereIn('id', $ids)->get();

        $result = [];
        if (!empty($datas)) {
            $result = $datas->toArray();
        }

        return $this->resObjectGet($result, 'data', $request->path());
    }

    /**
     * listByTaskRunLogId
     * 根据task_run_log_id查询data数据
     *
     * @param id
     * @return array
     */
    public function listByTaskRunLogId(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'task_run_log_id' => 'required|integer',
        ]);

        $datas = Data::where('task_run_log_id',$params['task_run_log_id'])->get();

        $result = [];
        if (!empty($datas)) {
            $result = $datas->toArray();
        }

        return $this->resObjectGet($result, 'data', $request->path());
    }

    /**
     * updateByTaskRunLogId
     * 根据task_run_log_id查询data数据
     *
     * @param id
     * @return array
     */
    public function updateByTaskRunLogId(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'task_run_log_id' => 'required|integer',
            'screenshot' => 'required|array',
        ]);

        $datas = Data::where('task_run_log_id',$params['task_run_log_id'])->get();

        foreach ($datas as $data) {
            $data->update($params);
        }

        $result = [];
        if (!empty($datas)) {
            $result = $datas->toArray();
        }

        return $this->resObjectGet($result, 'data', $request->path());
    }
}