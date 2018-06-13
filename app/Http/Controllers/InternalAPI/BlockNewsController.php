<?php
namespace App\Http\Controllers\InternalAPI;

use App\Models\BlockNews;
use App\Models\Requirement;
use Illuminate\Support\Facades\DB;
use Log;
use App\Services\ValidatorService;
use Illuminate\Http\Request;

/**
 * BlcokNewsController
 * 区块链新闻
 * @author huangxingxing@jinse.com
 * @version 1.0
 * Date: 2018/06/07
 */
class BlockNewsController extends Controller
{
    /**
     * all
     * 获取新闻列表
     *
     * @param id 需求ID
     * @param offset
     * @param limit
     * @param order
     * @param sort
     * @return array
     */
    public function all(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'requirement_id' => 'nullable|integer',
            'offset' => 'nullable|integer',
            'limit' => 'nullable|integer',
            'order' => 'nullable|string',
            'sort' => 'nullable|string',
        ]);

        if (empty($params['offset'])) {
            $params['offset'] = 0;
        }

        if (empty($params['limit'])) {
            $params['limit'] = 20;
        }

        if (empty($params['order'])) {
            $params['order'] = 'show_time';
        }

        if (empty($params['sort'])) {
            $params['sort'] = 'desc';
        }

        $total = BlockNews::where(function ($query) use ($params) {
            if (!empty($params['requirement_id'])) {
                $query->where('requirement_id', $params['requirement_id']);
            }
        })->count();

        $blockNews = BlockNews::where('id', '>', 0);

        if (!empty($params['requirement_id'])) {
            $blockNews->where('requirement_id', $params['requirement_id']);
        }
        $blockNews->take($params['limit']);
        $blockNews->skip($params['offset']);
        $blockNews->orderBy($params['order'], $params['sort']);

        $res = $blockNews->get();

        $result['total'] = $total;

        $data = [];
        if (!empty($blockNews)) {
            $data = $res->toArray();
        }

        $result['data'] = $data;
        return $this->resObjectGet($result, 'block_news', $request->path());
    }

    /**
     * getTotal
     * 获取当天24小时公司新闻数量
     *
     * @param id 需求ID
     * @param offset
     * @param limit
     * @param order
     * @param sort
     * @return array
     */
    public function getTotal(Request $request)
    {
        $startTime = time();
        $endTime = time() - 24*3600;
        $blockNewsTotal = BlockNews::select(DB::raw('count(*) as total, requirement_id'))
                ->where('show_time', '<=', date("Y-m-d H:i:s", $startTime))
                ->where('show_time', '>=', date("Y-m-d H:i:s", $endTime))
                ->groupBy('requirement_id')
                ->get();

        $result = [];

        if (!empty($blockNewsTotal)) {
            $result = $blockNewsTotal->toArray();
        }

        return $this->resObjectGet($result, 'block_news', $request->path());
    }
}
