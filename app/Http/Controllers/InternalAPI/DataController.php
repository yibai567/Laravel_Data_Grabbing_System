<?php
/**
 * DataController
 * 数据控制器
 * @author liqi@jinse.com
 * @version 1.0
 * Date: 2018/05/11
 */

namespace App\Http\Controllers\InternalAPI;

use App\Models\Data;
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
        Log::debug('[internal DataController create] start!');
        $params = $request->all();

        ValidatorService::check($params, [
            'company' => 'required|string|max:500',
            'content_type' => 'required|integer|between:1,10',
            'start_time' => 'required|date',
            'end_time' => 'required|date',
            'result' => 'required|array'
        ]);

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
                'detail_url' => $value['detail_url'],
                'show_time' => $value['show_time'],
                'read_count' => $value['read_count'],
                'status' => Data::STATUS_NORMAL,
                'start_time' => $params['start_time'],
                'end_time' => $params['end_time'],
                'created_time' => time(),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }

        if (!empty($newData)) {
            try {
                Data::insert($newData);
            } catch (\Exception $e) {
                Log::debug('[batchCreate] error message = ' . $e->getMessage());
                return response()->json(false);
            }
        }

        return response()->json(true);

    }
}