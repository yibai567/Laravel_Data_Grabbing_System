<?php

namespace App\Http\Controllers\WWW;

use App\Http\Controllers\Controller;
use App\Models\BlockNews;
use Illuminate\Http\Request;
use App\Services\ValidatorService;
use Log;
use DB;

class BlockNewsController extends Controller
{
    /**
     * index
     * 首页显示
     *
     * @return array
     */
    public function index($contentType = '')
    {
        $newData = [];

        //获取分类
        $newData['content_type'] = BlockNews::CONTENT_TYPES;

        if (empty($contentType)) {
            $contentType = BlockNews::CONTENT_TYPE_BANNER;
        }
        $newData['typeDefault'] = $contentType;

        $time = time() - 24*3600;
        //获取列表
        $res = BlockNews::where('status', BlockNews::STATUS_NORMAL)->where('content_type', $contentType)->where('created_time', '>', $time)->orderBy('id', 'desc')->take(2000)->get();

        $result = $res->toArray();
        if (empty($result)) {
            return view('block_news.index', $newData);
        }

        foreach ($result as $value) {
            $value['created_time'] = date('Y-m-d H:i:s', $value['created_time']);
            $newData['data'][$value['company']][] = $value;
        }
        return view('block_news.index', $newData);
    }


}
