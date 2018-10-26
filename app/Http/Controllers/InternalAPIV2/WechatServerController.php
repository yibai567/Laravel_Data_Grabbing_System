<?php
namespace App\Http\Controllers\InternalAPIV2;

use App\Events\StopWechatServerEvent;
use App\Models\V2\WechatServer;
use App\Services\EmailService;
use App\Services\WeWorkService;
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
            'wechat_name' => 'required|string|max:100',
            'room_name'   => 'required|string|max:100',
            'email'       => 'nullable|email',
            'listen_type' => 'required|integer|between:1,2',
        ]);

        if ($params['listen_type'] == WechatServer::LISTEN_TYPE_OFFICIAL && empty($params['email'])) {
            throw new \Dingo\Api\Exception\ResourceException('when selecting listener official, email is required');
        }

        $params['status'] = WechatServer::STATUS_INIT;

        $wechatServer = WechatServer::create($params);

        $result = $wechatServer->toArray();

        return $this->resObjectGet($result, 'wechat_server', $request->path());
    }

    /**
     * update
     *
     * @param $request
     * @return array
     */
    public function update(Request $request)
    {
        $params = $request->all();

        //验证参数
        ValidatorService::check($params, [
            'id'          => 'required|integer|max:1000',
            'wechat_name' => 'nullable|string|max:100',
            'room_name'   => 'nullable|string|max:100',
            'email'       => 'nullable|email',
            'listen_type' => 'nullable|integer|between:1,2',
        ]);

        $wechatServer = WechatServer::find($params['id']);

        if (empty($wechatServer)) {
            throw new \Dingo\Api\Exception\ResourceException('$wechatServer is not found');
        }

        if ($params['listen_type'] == WechatServer::LISTEN_TYPE_OFFICIAL && (empty($params['email']) && empty($wechatServer->email))) {
            throw new \Dingo\Api\Exception\ResourceException('when selecting listener official, email is required');
        }

        $wechatServer->update($params);

        $result = $wechatServer->toArray();

        return $this->resObjectGet($result, 'wechat_server', $request->path());
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

        if ($wechatServer->listen_type == WechatServer::LISTEN_TYPE_OFFICIAL) {
            //触发监听服务号停止事件
            event(new StopWechatServerEvent($wechatServer));
        }

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

    /**
     * sendMail
     * 发送企业微信消息
     *
     * @param
     * @return array
     */
    public function sendWework(Request $request)
    {

        $params = $request->all();

        ValidatorService::check($params, [
            'wechat_server_id'    => 'required|integer|max:999999999',
        ]);

        $wechatServer = WechatServer::find($params['wechat_server_id']);
        if (empty($wechatServer)) {
            Log::error('[InternalAPIv2 WechatServerController sendWework] $alarmResult is not found,wechat_server_id = ' . $params['wechat_server_id'] );
            return $this->resObjectGet(false, 'wechat_server', $request->path());
        }

        $mail = $wechatServer->email;

        $content = config('tpl.send_wework_notice');
        try {

            $weWork = new WeWorkService();
            $result = $weWork->pushMessage('text', $content, $mail);
            $result = json_decode($result, true);

            if ($result['errcode'] !== 0) {
                Log::error('[InternalAPIv2 WechatServerController sendWeWork]  企业微信发送通知异常');
                return $this->resObjectGet(false, 'wechat_server', $request->path());
            }

        } catch (\Exception $e) {
            Log::error('[InternalAPIv2 WechatServerController sendWeWork]  企业微信发送通知异常,message = ' . $e->getMessage());

            return $this->resObjectGet(false, 'wechat_server', $request->path());
        }

        return $this->resObjectGet(true, 'wechat_server', $request->path());
    }

    /**
     * sendMail
     * 发送邮件消息
     *
     * @param
     * @return array
     */
    public function sendEmail(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'wechat_server_id'    => 'required|integer|max:999999999',
        ]);

        $wechatServer = WechatServer::find($params['wechat_server_id']);

        if (empty($wechatServer)) {
            Log::error('[InternalAPIv2 WechatServerController sendMail] $wechatServer is not found,wechat_server_id = ' . $params['wechat_server_id'] );
            return $this->resObjectGet(false, 'wechat_server', $request->path());
        }

        $email = $wechatServer->email;

        if (empty($email)) {
            Log::error('[InternalAPIv2 WechatServerController sendMail] $email is not found');
            return $this->resObjectGet(false, 'wechat_server', $request->path());
        }

        $content = config('tpl.send_email_notice');

        $tpl = config('tpl.notice');

        try {

            $emailService = new EmailService();

            $result = $emailService->send($email, $content,$tpl);

            if (!$result) {
                Log::error('[InternalAPIv2 WechatServerController sendMail]  邮件发送通知异常');
                return $this->resObjectGet(false, 'wechat_server', $request->path());
            }

        } catch (\Exception $e) {
            Log::error('[InternalAPIv2 WechatServerController sendMail]  邮件发送通知异常,message = ' . $e->getMessage());

            return $this->resObjectGet(false, 'wechat_server', $request->path());
        }

        return $this->resObjectGet(true, 'wechat_server', $request->path());
    }

}
