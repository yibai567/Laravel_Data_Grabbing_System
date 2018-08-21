<?php
/**
 * FastNewsController
 * 模块控制器
 * @author liqi@jinse.com
 * @version 1.0
 * Date: 2018/08/14
 */

namespace App\Http\Controllers\InternalAPIV2;

use App\Models\V2\FastNews;
use App\Services\ValidatorService;
use Illuminate\Http\Request;

class FastNewsController extends Controller
{
    /**
     * all
     * 获取快讯列表
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

        $total = FastNews::where(function ($query) use ($params) {
            if (!empty($params['requirement_id'])) {
                $query->where('requirement_id', $params['requirement_id']);

            }
        })->where('created_at', '<=', date("Y-m-d H:i:s", $startTime))->where('created_at', '>=', date("Y-m-d H:i:s", $endTime))->count();

        $fastNews = FastNews::where('requirement_id', $params['requirement_id'])->where('created_at', '>=', date("Y-m-d H:i:s", $endTime));

        $fastNews->take($params['limit']);
        $fastNews->skip($params['offset']);
        $fastNews->orderBy('created_at', 'desc');

        $res = $fastNews->get();

        $result['total'] = $total;

        $data = [];
        if (!empty($fastNews)) {
            $data = $res->toArray();
        }

        $result['result'] = $data;
        return $this->resObjectGet($result, 'fast_news', $request->path());
    }
}