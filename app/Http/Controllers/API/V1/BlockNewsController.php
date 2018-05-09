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
        ValidatorService::check($params, [
            'company' => 'required|string|max:500',
            'content_type' => 'required|integer|between:1,10',
            'start_time' => 'required|date',
            'end_time' => 'required|date',
            'result' => 'required'
        ]);

        $newData = [];

        foreach ($params['result'] as $value) {
            $insertParams = [
                'content_type' => BlockNews::CONTENT_TYPE_BANNER,
                'company' => '',
                'title' => '',
                'md5_title' => '',
                'md5_content' => '',
                'content' => '',
                'detail_url' => '',
                'show_time' => '',
                'read_count' => '',
                'status' => BlockNews::STATUS_NORMAL,
                'start_time' => '',
                'end_time' => '',
                'created_time' => time(),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),

            ];
            if ($params['content_type'] == BlockNews::CONTENT_TYPE_LIVE) {
                if (empty($value['content'])) {
                    continue;
                }
            } else {
                if (empty($value['title'])) {
                    continue;
                }
            }

            if (!empty($value['content'])) {
                $insertParams['content'] = trim($value['content']);
                $insertParams['md5_content'] = md5($value['content']);
            }

            if (!empty($value['title'])) {
                $insertParams['title'] = trim($value['title']);
                $insertParams['md5_title'] = md5($value['title']);
            }

            if (empty($value['title'])) {
                $row = BlockNews::where('md5_content', $insertParams['md5_content'])
                                ->where('company', trim($params['company']))
                                ->where('content_type', trim($params['content_type']))
                                ->first();
            } else {
                $row = BlockNews::where('md5_title', $insertParams['md5_title'])
                                ->where('company', trim($params['company']))
                                ->where('content_type', trim($params['content_type']))
                                ->first();
            }
            if (!empty($row)) {
                if (!empty($value['read_count']) && $row->read_count != $value['read_count']) {
                    $row->read_count = $value['read_count'];
                    $row->updated_at = date('Y-m-d H:i:s');
                    $row->save();
                }
                continue;
            }

            $company = trim($params['company']);

            if (mb_strlen($company) > 50) {
                continue;
            }

            $content_type = trim($params['content_type']);

            if (!empty($content_type)) {
                $insertParams['content_type'] = $content_type;
            }
            $insertParams['company'] = $company;
            $insertParams['detail_url'] = $value['detail_url'];
            $insertParams['show_time'] = $value['show_time'];
            $insertParams['read_count'] = trim($value['read_count']);
            $insertParams['start_time'] = $params['start_time'];
            $insertParams['end_time'] = $params['end_time'];
            $newData[] = $insertParams;
        }

        if (empty($newData)) {
            return response()->json(true);
        }

        $data = [];
        foreach ($newData as $key => $value) {
            $isHas = false;
            if (!empty($data)) {
                foreach ($data as $item) {
                    if (!empty($value['md5_title'])) {
                        if ($item['md5_title'] == $value['md5_title']) {
                            $isHas = true;
                            continue 1;
                        }
                    }
                    if (!empty($value['md5_content'])) {
                        if ($item['md5_content'] == $value['md5_content']) {
                            $isHas = true;
                            continue 1;
                        }
                    }
                }
            }

            if (!$isHas) {
                $data[] = $value;
            }
        }

        if (!empty($data)) {
            try {
                BlockNews::insert($data);
            } catch (Exception $e) {
                return response()->json(false);
            }
        }

        return response()->json(true);
    }
}
