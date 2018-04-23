<?php

namespace App\Http\Controllers\InternalAPI;

use Log;
use App\Models\Item;
use App\Models\ItemRunLog;
use App\Services\ValidatorService;
use Illuminate\Http\Request;

class ItemRunLogController extends Controller
{

    /**
     * create
     * 创建
     *
     * @param
     * @return array
     */
    public function create(Request $request)
    {
        Log::debug('[internal ItemRunLogController create] start!');
        $params = $request->all();
        ValidatorService::check($params, [
            'item_id' => 'required|integer',
            'type' => 'required|integer|in:1,2',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date',
            'status' => 'nullable|integer|in:1,2,3',
        ]);

        if (empty($params['status'])) {
            $params['status'] = ItemRunLog::STATUS_RUNNING;
        }
        $itemRunLog = ItemRunLog::create($params);

        $result = [];
        if (!empty($itemRunLog)) {
            $result = $itemRunLog->toArray();
        }

        return $this->resObjectGet($result, 'item_run_log', $request->path());
    }

    /**
     * update
     * 更新
     *
     * @param
     * @return array
     */
    public function update(Request $request)
    {
        Log::debug('[internal ItemRunLogController update] start!');
        $params = $request->all();
        ValidatorService::check($params, [
            'id' => 'required|integer',
            'item_id' => 'nullable|integer',
            'type' => 'nullable|integer|in:1,2',
            'start_at' => 'nullable|datatime',
            'end_at' => 'nullable|datatime',
            'status' => 'nullable|integer|in:1,2,3',
        ]);

        $itemRunLog = ItemRunLog::find($params['id']);

        if (empty($itemRunLog)) {
            return $this->resError(405, 'itemRunLog is not exists!');
        }

        $itemRunLog->update($params);
        $result = $itemRunLog->toArray();

        return $this->resObjectGet($result, 'item_run_log', $request->path());
    }

    /**
     * all
     * 列表
     *
     * @param
     * @return array
     */
    public function all(Request $request)
    {
        Log::debug('[internal ItemRunLogController all] start!');
        $itemRunLogs = ItemRunLog::all();

        $result = [];
        if (!empty($itemRunLogs)) {
            $result = $itemRunLogs->toArray();
        }

        return $this->resObjectList($result, 'item_run_log', $request->path());
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
        Log::debug('[internal ItemRunLogController retrieve] start!');
        $params = $request->all();
        ValidatorService::check($params, [
            'id' => 'required|integer',
        ]);
        $itemRunLog = ItemRunLog::find($params['id']);

        $result = [];
        if (!empty($itemRunLog)) {
            $result = $itemRunLog->toArray();
        }

        return $this->resObjectGet($result, 'item_run_log', $request->path());
    }

    /**
     * @param Request $request
     */
    public function getByItemId(Request $request) {
        Log::debug('[internal ItemRunLogController getByItemId] start!');
        $params = $request->all();
        ValidatorService::check($params, [
            'item_id' => 'required|integer',
            'type' => 'required|integer|min:1|max:2',
        ]);

        $itemRunLogs = ItemRunLog::where('item_id', $params['item_id'])
            ->where('type', $params['type'])
            ->orderBy('id', 'desc')
            ->first();

        $result =[];
        if (!empty($itemRunLogs)) {
            $result = $itemRunLogs->toArray();
        }

        return $this->resObjectGet($result, 'item_run_log', $request->path());
    }

    /**
     * updateStatusSuccess
     * 修改状态为成功
     *
     * @param id
     * @return array
     */
    public function updateStatusSuccess(Request $request)
    {
        Log::debug('[internal ItemRunLogController updateStatusSuccess] start!');
        $params = $request->all();

        ValidatorService::check($params, [
            'id' => 'integer|required',
        ]);

        $res = ItemRunLog::find($params['id']);

        $itemRunLog = $res->toArray();

        if (empty($itemRunLog)) {
            throw new \Dingo\Api\Exception\ResourceException(" itemRunLog not exist");
        }

        $res->status = ItemRunLog::STATUS_SUCCESS;
        $res->save();
        $result = $res->toArray();

        return $this->resObjectGet($result, 'item_run_log', $request->path());
    }

    /**
     * updateStatusFail
     * 修改状态为失败
     *
     * @param id
     * @return array
     */
    public function updateStatusFail(Request $request)
    {
        Log::debug('[internal ItemRunLogController updateStatusFail] start!');
        $params = $request->all();

        ValidatorService::check($params, [
            'id' => 'integer|required',
        ]);

        $res = ItemRunLog::find($params['id']);

        $itemRunLog = $res->toArray();

        if (empty($itemRunLog)) {
            throw new \Dingo\Api\Exception\ResourceException(" itemRunLog not exist");
        }

        $res->status = ItemRunLog::STATUS_FAIL;
        $res->save();
        $result = $res->toArray();

        return $this->resObjectGet($result, 'item_run_log', $request->path());
    }
}
