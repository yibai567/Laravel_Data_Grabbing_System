<?php
/**
 * FastNewsController
 * 行业快讯控制器
 * @author liqi@jinse.com
 * @version 1.0
 * Date: 2018/08/14
 */

namespace App\Http\Controllers\WWW;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ValidatorService;
use App\Services\HttpService;
use Log;


class FastNewsController extends Controller
{
    /**
     * index
     * 首页显示
     *
     * @return array
     */
    public function index(Request $request)
    {
        $httpService = new HttpService();
        $fastNews = $httpService->get(config('url.jinse_open_url') . '/v2/fast_news/companies', []);
        $data = json_decode($fastNews->getContents(), true);
        $data['nav_status'] = 'fast_news';
        return view('fast_news.index', ["data" => $data]);
    }

    /**
     * getNewsByRequirementPoolId
     * 根据需求池id获取快讯列表
     *
     * @return array
     */

    public function getNewsByRequirementPoolId(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'requirement_id' => 'required|integer',
            'offset'         => 'nullable|integer',
            'limit'          => 'nullable|integer'
        ]);

        $httpService = new HttpService();
        $blockNews = $httpService->get(config('url.jinse_open_url') . '/v2/fast_news/requirement/', $params);

        return $blockNews->getContents();
    }
}