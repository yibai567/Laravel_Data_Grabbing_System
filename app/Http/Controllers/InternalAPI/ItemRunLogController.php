<?php

namespace App\Http\Controllers\InternalAPI;

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
        $params = $request->all();
        ValidatorService::check($params, [
            'item_id' => 'required|integer',
            'type' => 'required|integer|in:1,2',
            'start_at' => 'nullable|datatime',
            'end_at' => 'nullable|datatime',
            'status' => 'nullable|integer|in:1,2,3',
        ]);

        $result = [];

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
        $params = $request->all();
        ValidatorService::check($params, [
        ]);

        $result = [];

        return $this->resObjectGet($result, 'item_run_log', $request->path());
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
        ValidatorService::check($params, [
        ]);

        $result = [];

        return $this->resObjectGet($result, 'item_run_log', $request->path());
    }



}
