<?php

namespace App\Http\Controllers\API\V1;

use App\Models\BlockNews;
use Illuminate\Http\Request;
use App\Services\ValidatorService;
use Log;
use DB;

class BlockNewsController extends Controller
{
    /**
     * batchCreate
     * 批量插入
     *
     * @return array
     */
    public function batchCreate(Request $request)
    {
        $params = $request->all();
        Log::debug('[batchCreate] params = ', $params);
        ValidatorService::check($params, [
            'company' => 'required|string|max:500',
            'content_type' => 'required|integer|between:1,10',
            'start_time' => 'required|date',
            'end_time' => 'required|date',
            'result' => 'required'
        ]);

        $newData = [];

        foreach ($params['result'] as $value) {
            if ($params['content_type'] == BlockNews::CONTENT_TYPE_LIVE) {
                if (empty($value['content'])) {
                    Log::debug('[batchCreate] $params["result"] value empty');
                    continue;
                }
            } else {
                if (empty($value['title'])) {
                    Log::debug('[batchCreate] $params["result"] value empty');
                    continue;
                }
            }

            if (!empty($value['content'])) {
                $value['md5_content'] = md5($value['content']);
            }

            if (!empty($value['title'])) {
                $value['md5_title'] = md5($value['title']);
            }

            if (empty($value['title'])) {
                $row = BlockNews::where('md5_content', $value['md5_content'])->first();
            } else {
                $row = BlockNews::where('md5_title', $value['md5_title'])->first();
            }

            if (!empty($row)) {
                continue;
            }

            $value['company'] = $params['company'];
            $value['content_type'] = $params['content_type'];
            $value['start_time'] = $params['start_time'];
            $value['end_time'] = $params['end_time'];
            $value['status'] = BlockNews::STATUS_NORMAL;
            $value['created_time'] = time();
            $newData[] = $value;
        }

        if (!empty($newData)) {
            try {
                BlockNews::insert($newData);
            } catch (Exception $e) {
                Log::debug('[batchCreate] error message = ' . $e->getMessage());
                return response()->json(false);
            }
        }

        return response()->json(true);
    }
}
