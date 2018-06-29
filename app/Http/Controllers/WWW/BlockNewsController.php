<?php

namespace App\Http\Controllers\WWW;

use App\Http\Controllers\Controller;
use App\Models\BlockNews;
use Illuminate\Http\Request;
use App\Services\ValidatorService;
use App\Services\HttpService;
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
    // public function index($contentType = '')
    // {
    //     $newData = [];

    //     //获取分类
    //     $newData['content_type'] = BlockNews::CONTENT_TYPES;

    //     if (empty($contentType)) {
    //         $contentType = BlockNews::CONTENT_TYPE_BANNER;
    //     }
    //     $newData['typeDefault'] = $contentType;

    //     $time = time() - 24*3600;
    //     //获取列表
    //     $res = BlockNews::where('status', BlockNews::STATUS_NORMAL)->where('content_type', $contentType)->where('created_time', '>', $time)->orderBy('id', 'desc')->take(5000)->get();

    //     $result = $res->toArray();
    //     if (empty($result)) {
    //         return view('block_news.index', $newData);
    //     }

    //     foreach ($result as $value) {
    //         $value['created_time'] = date('Y-m-d H:i:s', $value['created_time']);
    //         $newData['data'][$value['company']][] = $value;
    //     }
    //     return view('block_news.index', $newData);
    // }

    /**
     * index
     * 首页显示
     *
     * @return array
     */
    public function index(Request $request)
    {
        $params = $request->all();
        ValidatorService::check($params, [
            'requirement_id' => 'nullable|integer',
            'offset' => 'nullable|integer',
            'limit' => 'nullable|integer',
            'order' => 'nullable|string',
            'sort' => 'nullable|string',
        ]);

        $data = [];

        $httpService = new HttpService();
        $blockNews = $httpService->get(config('url.jinse_open_url') . '/v1/block_news', $params);
        $blockNews = json_decode($blockNews->getContents(), true);

        $data['block_news'] = $blockNews['data'];
        $companies = $httpService->get(config('url.jinse_open_url') . '/v1/block_news/companies', []);
        $companies = json_decode($companies->getContents(), true);

        $data['companies'] = $companies['data'];

        if (empty($params['offset'])) {
            $data['offset'] = 0;
        }
        if (empty($params['limit'])) {
            $data['limit'] = 20;
        }
        if (empty($params['order'])) {
            $data['order'] = 'show_time';
        }
        if (empty($params['sort'])) {
            $data['sort'] = 'desc';
        }
        if (!empty($params['requirement_id'])) {
            $data['requirement_id'] = $params['requirement_id'];
        }
        return view('block_news.block', ["data" => $data]);
    }

    /**
     * ajaxList
     * 首页显示
     *
     * @return array
     */
    public function ajaxList(Request $request)
    {
        $params = $request->all();
        ValidatorService::check($params, [
            'requirement_id' => 'nullable|integer',
            'offset' => 'nullable|integer',
            'limit' => 'nullable|integer',
            'order' => 'nullbale|string',
            'sort' => 'nullbale|nullbale',
        ]);
        $data = [];
        $httpService = new HttpService();
        $blockNews = $httpService->get(config('url.jinse_open_url') . '/v1/block_news', $params);
        $blockNews = json_decode($blockNews->getContents(), true);

        $blockNews['offset'] = $params['offset'];
        $blockNews['limit'] = $params['limit'];
        $blockNews['order'] = $params['order'];
        $blockNews['sort'] = $params['sort'];
        $blockNews['requirement_id'] = $params['requirement_id'];

        echo json_encode($blockNews);
    }

    /**
     * index
     * 首页显示
     *
     * @return array
     */
    public function all(Request $request)
    {
        $data = [];
        $httpService = new HttpService();
        $blockNews = $httpService->get(config('url.jinse_open_url') . '/v2/news', []);
        $data = json_decode($blockNews->getContents(), true);
        $data['nav_status'] = 'block_news';
        return view('block_news.block_v2', ["data" => $data]);
    }

    public function ajaxNewList(Request $request, $id)
    {
        $data = [];
        $httpService = new HttpService();
        $blockNews = $httpService->get(config('url.jinse_open_url') . '/v2/block_news/' . $id);
        echo $blockNews->getContents();
    }


}
