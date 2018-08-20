<?php
namespace App\Http\Controllers\InternalAPIV2;

use App\Models\BlockNews;
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
     * getByRequirementId
     * 获取新闻列表
     *
     * @return array
     */

    public function all(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'requirement_id' => 'nullable|integer',
            'offset'         => 'nullable|integer',
            'limit'          => 'nullable|integer',
        ]);

        if (empty($params['offset'])) {
            $params['offset'] = 0;
        }

        if (empty($params['limit'])) {
            $params['limit'] = 20;
        }

        $startTime = time();
        $endTime = time() - 24*3600;

        $total = BlockNews::where(function ($query) use ($params) {
            if (!empty($params['requirement_id'])) {
                $query->where('requirement_id', $params['requirement_id']);

            }
        })->where('created_at', '<=', date("Y-m-d H:i:s", $startTime))->where('created_at', '>=', date("Y-m-d H:i:s", $endTime))->count();

        $fastNews = BlockNews::where('requirement_id', $params['requirement_id'])->where('created_at', '>=', date("Y-m-d H:i:s", $endTime));

        $fastNews->take($params['limit']);
        $fastNews->skip($params['offset']);
        $fastNews->orderBy('show_time', 'desc')->orderBy('created_at', 'desc');

        $res = $fastNews->get();

        $result['total'] = $total;

        $data = [];
        if (!empty($fastNews)) {
            $data = $res->toArray();
        }

        $result['result'] = $data;
        return $this->resObjectGet($result, 'block_news', $request->path());
    }
}
