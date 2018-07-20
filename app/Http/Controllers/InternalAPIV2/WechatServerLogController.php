<?php
namespace App\Http\Controllers\InternalAPIV2;

use App\Models\V2\WechatServerLog;
use Illuminate\Http\Request;
use App\Services\ValidatorService;

use Log;

class WechatServerLogController extends Controller
{

    /**
     * crete
     *
     * @param $request
     * @return array
     */
    public function create(Request $request)
    {
        $params = $request->all();

        //验证参数
        ValidatorService::check($params, [
            'wechat_server_id'   => 'required|integer',
            'content'   => 'nullable|string',
        ]);

        $wechatServerLog = WechatServerLog::create($params);

        return $this->resObjectGet($wechatServerLog, 'wechat_server_log', $request->path());
    }

    /**
     * listByWechatServerId
     *
     * @param $request
     * @return array
     */
    public function listByWechatServerId(Request $request, $wechat_server_id)
    {
        $params = $request->all();
        ValidatorService::check($params, [
            'last_time'   => 'date',
        ]);
        if (isset($params['asc'])) {
            $sort = 'asc';
        } else {
            $sort = 'desc';
        }
        $wechatServerLog = WechatServerLog::where('wechat_server_id', $wechat_server_id)
                ->where(function ($query) use ($params) {
                    if (!empty($params['last_time'])) {
                        $query->where('created_at', '>=', $params['last_time']);
                    }
                    if (!empty($params['log_id'])) {
                        $query->where('id', '>', $params['log_id']);
                    }
                })
                ->orderBy('id', $sort)
                ->limit(500)
                ->get();
        if (empty($wechatServerLog)) {
            return $this->resObjectGet([], 'wechat_server_log', $request->path());
        }
        $wechatServerLog = $wechatServerLog->toArray();

        return $this->resObjectGet($wechatServerLog, 'wechat_server_log', $request->path());
    }
}
