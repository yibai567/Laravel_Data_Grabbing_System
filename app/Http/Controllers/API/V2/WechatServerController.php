<?php
/**
 * WechatServerController
 * 微信消息处理
 * @author huangxingxing@jinse.com
 * @version 1.0
 * Date: 2018/06/26
 */

namespace App\Http\Controllers\API\V2;

use Log;
use App\Services\InternalAPIV2Service;
use App\Services\ValidatorService;
use Illuminate\Http\Request;

class WechatServerController extends Controller
{
    /**
     * create
     * 保存微信服务记录
     *
     * @param
     * @return array
     */
    public function createLog(Request $request)
    {
        $params = $request->all();
        Log::debug('$params', $params);
        //验证参数
        ValidatorService::check($params, [
            'wechat_server_id'   => 'required|integer',
            'content'   => 'nullable|string',
        ]);
        try {
            $wechatServerLog = InternalAPIV2Service::post('/wechat_server_log', ['wechat_server_id' => $params['wechat_server_id'], 'content' => $params['content']]);
            dd($wechatServerLog);
            if (empty($wechatServerLog)) {
                throw new \Dingo\Api\Exception\ResourceException("wechatServerLog create fail");
            }
        } catch (\Exception $e) {
            throw new \Dingo\Api\Exception\ResourceException("wxMessage create fail");
        }

        return $this->resObjectGet(true, '/wechat_server_log', $request->path());
    }




    /**
     * getGroupProblem
     * 获取分组问题
     *
     * @param
     * @return array
     */
    public function getMessageById(Request $request, $id)
    {
        $result = InternalAPIV2Service::get('/wx/room/message/' . $id);
        return $this->resObjectGet($result, 'WxMessage', $request->path());
    }

    /**
     * stop
     * 停止服务
     *
     * @param
     * @return array
     */
    public function stop(Request $request)
    {
        $params = $request->all();
        //验证参数
        ValidatorService::check($params, [
            'id'   => 'required|integer',
        ]);
        $result = InternalAPIV2Service::post('/wechat_server/stop', ['id' => $params['id']]);
        return $this->resObjectGet($result, 'WxMessage', $request->path());
    }

}
