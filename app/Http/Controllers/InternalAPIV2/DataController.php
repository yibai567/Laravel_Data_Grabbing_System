<?php
/**
 * DataController
 * 数据控制器
 * @author liqi@jinse.com
 * @version 1.0
 * Date: 2018/06/15
 */

namespace App\Http\Controllers\InternalAPIV2;

use App\Models\V2\Data;
use App\Models\V2\Task;
use App\Models\V2\TaskStatistics;
use App\Events\StatisticsEvent;
use Log;
use App\Services\ValidatorService;
use Illuminate\Http\Request;


class DataController extends Controller
{
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
            'company'         => 'required|string|max:50',
            'content_type'    => 'required|integer|between:1,9',
            'task_run_log_id' => 'required|integer|max:999999999',
            'task_id'         => 'required|integer|max:5000',
            'start_time'      => 'required|date',
            'end_time'        => 'required|date',
            'result'          => 'required|array'
        ]);

        $task = Task::find($params['task_id']);

        if (empty($task)) {
            Log::debug('[InternalAPIV2 DataController batchSave]  $task is not found,task_id : ' . $params['task_id']);
            return $this->resObjectGet(false, 'data', $request->path());
        }
        $result = [];
        $result['task_id'] = $params['task_id'];
        $result['data'] = [];
        $same = 0;

        foreach ($params['result'] as $value) {

            ValidatorService::check($value, [
                'title'      => 'nullable|string|max:2000',
                'description'=> 'nullable|string|max:2000',
                'content'    => 'nullable|string',
                'detail_url' => 'nullable|string|max:500',
                'show_time'  => 'nullable|string|max:100',
                'author'     => 'nullable|string|max:100',
                'read_count' => 'nullable|string|max:100',
                'images'     => 'nullable|string|max:500',
            ]);

            if (empty($value['title']) && empty($value['content'])) {
                continue;
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

            //检测数据库是否已存在数据
            if (empty($value['title'])) {
                $row = Data::where('md5_content', $value['md5_content'])->where('task_id',$params['task_id'])->first();
            } else {
                $row = Data::where('md5_title', $value['md5_title'])->where('task_id',$params['task_id'])->first();
            }
            //内容已存在,只更新updated_at，证明脚本可以抓取到内容
            if (!empty($row)) {
                if ($same < 1) {
                    $row->updated_at = date('Y-m-d H:i:s', time());
                    $row->save();
                    $same++;
                }
                continue;
            }

            if (!empty($value['images'])) {
                $value['images'] = explode(',', $value['images']);
            }


            //整理保存数据
            $createData = [
                'content_type'    => $params['content_type'],
                'company'         => $params['company'],
                'title'           => $value['title'],
                'md5_title'       => $value['md5_title'],
                'md5_content'     => $value['md5_content'],
                'content'         => $value['content'],
                'description'     => $value['description'],
                'task_id'         => $params['task_id'],
                'task_run_log_id' => $params['task_run_log_id'],
                'detail_url'      => $value['detail_url'],
                'show_time'       => $value['show_time'],
                'author'          => $value['author'],
                'read_count'      => $value['read_count'],
                'thumbnail'       => $value['images'],
                'language_type'   => $task->language_type,
                'status'          => Data::STATUS_NORMAL,
                'start_time'      => $params['start_time'],
                'end_time'        => $params['end_time'],
                'created_time'    => time()
            ];

            $datum = Data::create($createData);
            $result['data'][]['id'] =  $datum->id;
        }
        $newData = [
            "type" => TaskStatistics::TYPE_TASK,
            "data" => ["task_id" => $params['task_id'], "task_run_log_id" => $params['task_run_log_id']]
        ];
        event(new StatisticsEvent($newData));
        return $this->resObjectGet($result, 'data', $request->path());
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
        $datum = Data::whereIn('id', $ids)->get();

        $result = [];
        if (!empty($datum)) {
            $result = $datum->toArray();
        }

        return $this->resObjectGet($result, 'data', $request->path());
    }

    /**
     * updateByTaskRunLogId
     * 根据task_run_log_id更新截图
     *
     * @param id
     * @return array
     */
    public function updateByTaskRunLogId(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'task_run_log_id' => 'required|integer|max:999999999',
            'screenshot' => 'required|array',
        ]);

        $data = Data::where('task_run_log_id',$params['task_run_log_id'])->get();

        foreach ($data as $datum) {
            $datum->update($params);
        }

        $result = [];
        if (!empty($data)) {
            $result = $data->toArray();
        }

        return $this->resObjectGet($result, 'data', $request->path());
    }

}