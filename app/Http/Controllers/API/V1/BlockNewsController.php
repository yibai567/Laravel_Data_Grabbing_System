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
            'company'      => 'required|string|min:1|max:100',
            'content_type' => 'required|integer|between:1,10',
            'start_time'   => 'required|date',
            'end_time'     => 'required|date',
            'result'       => 'required'
        ]);

        $newData     = [];
        $contentType = intval($params['content_type']);
        $company     = trim($params['company']);
        $startTime   = trim($params['start_time']);
        $endTime     = trim($params['end_time']);

        if (empty($params['result']) || !is_array($params['result'])) {
            return response()->json(true);
        }

        foreach ($params['result'] as $value) {
            $readCount  = trim($value['read_count']);
            $showTime   = trim($value['show_time']);
            $detailUrl  = trim($value['detail_url']);
            $title      = trim($value['title']);
            $content    = trim($value['content']);
            $md5Title   = '';
            $md5Content = '';

            if (empty($content) && empty($title)) {
                continue;
            }
            // 长度等标准判断 TODO
            if (mb_strlen($title) > 255) {
                continue;
            }

            if (strlen($title) > 65535) {
                continue;
            }

            if (!empty($content)) {
                $md5Content = md5($content);
            }

            if (!empty($title)) {
                $md5Title = md5($title);
            }

            if (empty($md5Title)) {
                $row = BlockNews::where('md5_content', $md5Content);
            } else {
                $row = BlockNews::where('md5_title', $md5Title);
            }

            $blockNews = $row->where('company', $company)->where('content_type', $contentType)->first();

            if (!empty($blockNews)) {
                if (!empty($readCount) && $blockNews->read_count != $readCount) {
                    $blockNews->read_count = $readCount;
                    $blockNews->updated_at = date('Y-m-d H:i:s');
                    $blockNews->save();
                }
                continue;
            }

            $insertParams = [
                'content_type' => $contentType,
                'company'      => $company,
                'title'        => $title,
                'md5_title'    => $md5Title,
                'md5_content'  => $md5Content,
                'content'      => $content,
                'detail_url'   => $detailUrl,
                'show_time'    => $showTime,
                'read_count'   => $readCount,
                'status'       => BlockNews::STATUS_NORMAL,
                'start_time'   => $startTime,
                'end_time'     => $endTime,
                'created_time' => time(),
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ];

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
