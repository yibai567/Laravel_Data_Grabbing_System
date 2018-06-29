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
     * @param $requirement_id
     * @return array
     */
    public function getByRequirementId(Request $request, $requirement_id)
    {
        $startTime = time();
        $endTime = time() - 24*3600;

        $res = BlockNews::where('requirement_id', $requirement_id)
                            ->where('created_at', '<=', date("Y-m-d H:i:s", $startTime))
                            ->where('created_at', '>=', date("Y-m-d H:i:s", $endTime))
                            ->get();
        $result = [];
        if (!empty($res)) {
            $result = $res->toArray();
        }
        return $this->resObjectGet($result, 'block_news', $request->path());
    }
}
