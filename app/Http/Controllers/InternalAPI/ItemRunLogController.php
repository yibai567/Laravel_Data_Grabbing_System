<?php

namespace App\Http\Controllers\InternalAPI;

use App\Models\Item;
use App\Models\ItemRunLog;
use App\Services\ValidatorService;
use Illuminate\Http\Request;

class ItemRunLogController extends Controller
{

    /**
     * create
     * 保存
     *
     * @param
     * @return array
     */
    public function create(Request $request)
    {
        $params = $request->all();
        ValidatorService::check($params, [
            'item_id' => 'required|integer',
            'type' => 'required|integer|in:1,2',
            'start_at' => 'nullable|datatime',
            'end_at' => 'nullable|datatime',
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
    public function update(Request $request, $id)
    {
        $id = intval($id);
        $params = $request->all();
        ValidatorService::check($params, [
            'item_id' => 'nullable|integer',
            'type' => 'nullable|integer|in:1,2',
            'start_at' => 'nullable|datatime',
            'end_at' => 'nullable|datatime',
            'status' => 'nullable|integer|in:1,2,3',
        ]);

        $itemRunLog = ItemRunLog::find($id);

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
        $result = [];

        $itemRunLogs = ItemRunLog::all();
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
    public function retrieve(Request $request, $id)
    {
        $id = intval($id);
        $itemRunLog = ItemRunLog::find($id);

        $result = [];
        if (!empty($itemRunLog)) {
            $result = $itemRunLog->toArray();
        }

        return $this->resObjectGet($result, 'item_run_log', $request->path());
    }

    /**
     * @param Request $request
     */
    public function getByItemId(Request $request, $itemId) {
        $itemId = intval($itemId);
        $params = $request->all();
        ValidatorService::check($params, [
            'type' => 'required|integer|min:1|max:2',
        ]);

        $itemRunLogs = ItemRunLog::where('item_id', $itemId)
            ->where('type', $params['type'])
            ->orderBy('id', 'desc')
            ->first();

        $result =[];
        if (!empty($itemRunLogs)) {
            $result = $itemRunLogs->toArray();
        }

        return $this->resObjectGet($result, 'item_run_log', $request->path());
    }
}
