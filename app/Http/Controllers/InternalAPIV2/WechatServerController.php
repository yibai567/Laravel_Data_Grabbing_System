<?php
namespace App\Http\Controllers\InternalAPIV2;

use App\Models\V2\WechatServer;
use Illuminate\Http\Request;
use App\Services\ValidatorService;
use App\Events\WechatServerEvent;

use Log;

class WechatServerController extends Controller
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
            'wechat_name'   => 'required|string|max:100',
            'room_name'   => 'required|string|max:100',
            'listen_type'   => 'required|integer|between:1,2',
        ]);

        $params['status'] = WechatServer::STATUS_INIT;

        $wechatServer = WechatServer::create($params);

        return $this->resObjectGet($wechatServer, 'wechat_server', $request->path());
    }

    /**
     * statusToStart
     * 启动服务
     *
     * @param
     * @return array
     */
    public function statusToStart(Request $request)
    {
        $params = $request->all();

        //验证参数
        ValidatorService::check($params, [
            'id'   => 'required|integer',
        ]);

        $wechatServer = WechatServer::find($params['id']);

        if (empty($wechatServer)) {
            throw new \Dingo\Api\Exception\ResourceException('this id is not found');
        }

        if (!in_array($wechatServer->status, [WechatServer::STATUS_INIT, WechatServer::STATUS_STOP])) {
            throw new \Dingo\Api\Exception\ResourceException('this status is error');
        }

        $wechatServer->status = WechatServer::STATUS_START;
        $wechatServer->start_at = date('Y-m-d H:i:s');
        $wechatServer->save();
        // 插入服务队列事件
        event(new WechatServerEvent($wechatServer));

        return $this->resObjectGet($wechatServer, 'wechat_server', $request->path());
    }

    /**
     * statusToStop
     * 停止服务
     *
     * @param
     * @return array
     */
    public function statusToStop(Request $request)
    {
        $params = $request->all();

        //验证参数
        ValidatorService::check($params, [
            'id'   => 'required|integer',
        ]);

        $wechatServer = WechatServer::find($params['id']);

        if (empty($wechatServer)) {
            throw new \Dingo\Api\Exception\ResourceException('this id is not found');
        }

        if ($wechatServer->status != WechatServer::STATUS_START) {
            throw new \Dingo\Api\Exception\ResourceException('this status is error');
        }

        $wechatServer->status = WechatServer::STATUS_STOP;
        $wechatServer->stop_at = date('Y-m-d H:i:s');
        $wechatServer->save();

        // 插入队列事件
        event(new WechatServerEvent($wechatServer));

        return $this->resObjectGet($wechatServer, 'wechat_server', $request->path());
    }

    /**
     * retrieve
     * 详情
     *
     * @param
     * @return array
     */
    public function retrieve(Request $request, $id)
    {
        $wechatServer = WechatServer::find($id);

        $result = [];

        if (!empty($wechatServer)) {
            $result = $wechatServer->toArray();
        }

        return $this->resObjectGet($result, 'wechatServer', $request->path());
    }

}
