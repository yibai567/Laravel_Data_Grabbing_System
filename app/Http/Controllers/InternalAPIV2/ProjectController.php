<?php
/**
 * ProjectController
 * 分发项目控制器
 * @author liqi@jinse.com
 * @version 1.0
 * Date: 2018/06/15
 */

namespace App\Http\Controllers\InternalAPIV2;


use App\Models\V2\Data;
use App\Models\V2\Project;
use App\Models\V2\ProjectResult;
use App\Events\ProjectResultEvent;
use App\Services\ValidatorService;
use Illuminate\Http\Request;
use Log;

class ProjectController extends Controller
{
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
            'id' => 'required|integer|max:999999999',
        ]);

        $project = Project::find($params['id']);

        $result = [];

        if (!empty($project)) {
            $result = $project->toArray();
        }

        return $this->resObjectGet($result, 'project', $request->path());
    }

    /**
     * liveList
     * 快讯列表项目
     *
     * @param
     * @return boolean
     */
    public function liveList(Request $request)
    {
        Log::debug('[v2 ProjectController liveList] start');
        $params = $request->all();

        ValidatorService::check($params, [
            'data_id'    => 'required|integer|max:999999999',
            'project_id' => 'required|integer|max:999999999',
        ]);

        //查询data数据
        $data = Data::find($params['data_id']);

        if (empty($data)) {
            Log::debug('[InternalAPIv2 ProjectController liveList] $data is not found,data_id = ' . $params['data_id']);
            return $this->resObjectGet(false, 'live_list', $request->path());
        }

        try {
            //整理保存数据
            $newData = [
                'content_type'    => $data->content_type,
                'company'         => $data->company,
                'task_id'         => $data->task_id,
                'project_id'      => $params['project_id'],
                'task_run_log_id' => $data->task_run_log_id,
                'title'           => $data->title,
                'md5_title'       => $data->md5_title,
                'md5_content'     => $data->md5_content,
                'content'         => $data->content,
                'detail_url'      => $data->detail_url,
                'show_time'       => $data->show_time,
                'author'          => $data->author,
                'read_count'      => $data->read_count,
                'thumbnail'       => $data->thumbnail,
                'screenshot'      => $data->screenshot,
                'status'          => $data->status,
                'start_time'      => $data->start_time,
                'end_time'        => $data->end_time,
                'created_time'    => $data->created_time,
            ];

            $projectResult = ProjectResult::create($newData);

            $result['project_result_id'] = $projectResult->id;
            Log::debug('[InternalAPIv2 ProjectController liveList] $result = ', $result);
            if (!empty($projectResult)) {
                //分发projectResult事件
                event(new ProjectResultEvent($result));
            }
        } catch (\Exception $e) {
            Log::debug('[InternalAPIv2 ProjectController live_list] error message = ' . $e->getMessage());
        }


        return $this->resObjectGet(true, 'live_list', $request->path());
    }

    /**
     * liveDetail
     * 快讯详情项目
     *
     * @param
     * @return boolean
     */
    public function liveDetail(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'data_id'    => 'required|integer|max:999999999',
            'project_id' => 'required|integer|max:999999999',
        ]);

        //查询data数据
        $data = Data::find($params['data_id']);

        if (empty($data)) {
            Log::debug('[InternalAPIv2 ProjectController liveList] $data is not found,data_id = ' . $params['data_id']);
            return $this->resObjectGet(false, 'live_detail', $request->path());
        }

        try {
            //整理保存数据
            $newData = [
                'content_type'    => $data->content_type,
                'company'         => $data->company,
                'task_id'         => $data->task_id,
                'project_id'      => $params['project_id'],
                'task_run_log_id' => $data->task_run_log_id,
                'title'           => $data->title,
                'md5_title'       => $data->md5_title,
                'md5_content'     => $data->md5_content,
                'content'         => $data->content,
                'detail_url'      => $data->detail_url,
                'show_time'       => $data->show_time,
                'author'          => $data->author,
                'read_count'      => $data->read_count,
                'thumbnail'       => $data->thumbnail,
                'screenshot'      => $data->screenshot,
                'status'          => $data->status,
                'start_time'      => $data->start_time,
                'end_time'        => $data->end_time,
                'created_time'    => $data->created_time,
            ];

            $projectResult = ProjectResult::create($newData);

            $result['project_result_id'] = $projectResult->id;

            if (!empty($projectResult)) {
                //分发projectResult事件
                event(new ProjectResultEvent($result));
            }

        } catch (\Exception $e) {
            Log::debug('[InternalAPIv2 ProjectController liveDetail] error message = ' . $e->getMessage());
        }


        return $this->resObjectGet(true, 'live_detail', $request->path());
    }

    /**
     * blockNew
     * 新闻块项目
     *
     * @param
     * @return boolean
     */
    public function blockNew(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'data_id'    => 'required|integer|max:999999999',
            'project_id' => 'required|integer|max:999999999',
        ]);

        //查询data数据
        $data = Data::find($params['data_id']);

        if (empty($data)) {
            Log::debug('[InternalAPIv2 ProjectController liveList] $data is not found,data_id = ' . $params['data_id']);
            return $this->resObjectGet(false, 'live_detail', $request->path());
        }

        try {
            //整理保存数据
            $newData = [
                'content_type'    => $data->content_type,
                'company'         => $data->company,
                'task_id'         => $data->task_id,
                'project_id'      => $params['project_id'],
                'task_run_log_id' => $data->task_run_log_id,
                'title'           => $data->title,
                'md5_title'       => $data->md5_title,
                'md5_content'     => $data->md5_content,
                'content'         => $data->content,
                'detail_url'      => $data->detail_url,
                'show_time'       => $data->show_time,
                'author'          => $data->author,
                'read_count'      => $data->read_count,
                'thumbnail'       => $data->thumbnail,
                'screenshot'      => $data->screenshot,
                'status'          => $data->status,
                'start_time'      => $data->start_time,
                'end_time'        => $data->end_time,
                'created_time'    => $data->created_time,
            ];

            $projectResult = ProjectResult::create($newData);

            $result['project_result_id'] = $projectResult->id;
            Log::debug('[v2 ProjectController liveDetail] $result = ', $result);
            if (!empty($projectResult)) {
                //分发projectResult事件
                event(new ProjectResultEvent($result));
            }

        } catch (\Exception $e) {
            Log::debug('[v2 ProjectController liveDetail] error message = ' . $e->getMessage());
        }


        return $this->resObjectGet(true, 'block_news', $request->path());
    }

}