<?php
/**
 * WxMessageController
 * 微信消息处理
 * @author huangxingxing@jinse.com
 * @version 1.0
 * Date: 2018/06/26
 */

namespace App\Http\Controllers\API\V2;

use App\Events\SaveDataEvent;
use Log;
use App\Models\V2\WxOfficialMessage;
use App\Services\InternalAPIV2Service;
use App\Services\ValidatorService;
use Illuminate\Http\Request;

class WxMessageController extends Controller
{
    /**
     * create
     * 保存微信信息
     *
     * @param
     * @return array
     */
    public function create(Request $request)
    {
        $params = $request->all();
        //验证参数
        ValidatorService::check($params, [
            'contact_name' => 'required|string|max:100',
            'room_name' => 'required|string|max:100',
            'content' => 'required|string|max:1000',
            'date_time' => 'nullable|string|date',
        ]);

        $wxMessageParams = [
            "contact_name" => $params['contact_name'],
            "room_name" => $params['room_name'],
            "content" => $params['content'],
        ];
        try {
            $messageGroup = InternalAPIV2Service::post('/wx/message/group', ['date_time' => $params['date_time']]);
            if (empty($messageGroup)) {
                throw new \Dingo\Api\Exception\ResourceException("messageGroup create fail");
            }
            $wxMessageParams['group_wx_message_id'] = $messageGroup['id'];
            $wxMessage = InternalAPIV2Service::post('/wx/message', $wxMessageParams);
            if (empty($wxMessage)) {
                throw new \Dingo\Api\Exception\ResourceException("wxMessage create fail");
            }

        } catch (\Exception $e) {
            throw new \Dingo\Api\Exception\ResourceException("wxMessage create fail");
        }

        return $this->resObjectGet(true, 'WxMessage', $request->path());
    }

    /**
     * updateGroupStatus
     * 修改分组状态
     *
     * @param
     * @return array
     */
    public function updateGroupStatus(Request $request)
    {
        $params = $request->all();
        //验证参数
        ValidatorService::check($params, [
            'id' => 'required|integer'
        ]);

        try {
            $result = InternalAPIV2Service::post('/wx/message/group/status', $params);
        } catch (\Exception $e) {
            throw new \Dingo\Api\Exception\ResourceException("request /wx/message/group/status error");
        }
        return $this->resObjectGet($result, 'WxMessage', $request->path());
    }

    /**
     * updateStatus
     * 修改分组状态
     *
     * @param
     * @return array
     */
    public function updateStatus(Request $request)
    {
        $params = $request->all();
        //验证参数
        ValidatorService::check($params, [
            'id' => 'required|integer'
        ]);

        try {
            $result = InternalAPIV2Service::post('/wx/message/status', $params);
        } catch (\Exception $e) {
            throw new \Dingo\Api\Exception\ResourceException("wxMessage update fail");
        }
        return $this->resObjectGet($result, 'WxMessage', $request->path());
    }

    /**
     * allGroup
     * 显示分组列表
     *
     * @param
     * @return array
     */
    public function allGroup(Request $request)
    {
        $params = $request->all();
        ValidatorService::check($params, [
            'status' => 'nullable|integer|between:1,2',
            'offset' => 'nullable|integer',
            'limit' => 'nullable|integer',
            'order' => 'nullable|string',
            'sort' => 'nullable|string',
        ]);

        try {
            $result = InternalAPIV2Service::get('/wx/message/group', $params);
        } catch (\Exception $e) {
            throw new \Dingo\Api\Exception\ResourceException("request /wx/message/group error");
        }

        return $this->resObjectGet($result, 'wxMessage', $request->path());
    }

    /**
     * all
     * 显示数据列表
     *
     * @param
     * @return array
     */
    public function all(Request $request)
    {
        $params = $request->all();
        ValidatorService::check($params, [
            'group_wx_message_id' => 'required|integer',
            'offset' => 'nullable|integer',
            'limit' => 'nullable|integer',
            'order' => 'nullable|string',
            'sort' => 'nullable|string',
            'status' => 'nullable|integer'
        ]);

        try {
            $result = InternalAPIV2Service::get('/wx/message', $params);
        } catch (\Exception $e) {
            throw new \Dingo\Api\Exception\ResourceException("request /wx/message error");
        }

        return $this->resObjectGet($result, 'wxMessage', $request->path());
    }

    /**
     * newCreate
     * 新版保存微信信息
     *
     * @param
     * @return array
     */
    public function newCreate(Request $request)
    {
        $params = $request->all();
        //验证参数
        ValidatorService::check($params, [
            'contact_name' => 'required|string|max:100',
            'room_name' => 'required|string|max:100',
            'content' => 'required|string|max:1000',
        ]);

        try {
            $result = InternalAPIV2Service::post('/wx/room/message', $params);
        } catch (\Exception $e) {
            throw new \Dingo\Api\Exception\ResourceException("wxMessage create fail");
        }
        return $this->resObjectGet($result, 'WxMessage', $request->path());
    }
    /**
     * getGroupProblem
     * 获取分组问题
     *
     * @param
     * @return array
     */
    public function getGroupProblem(Request $request)
    {
        $result = InternalAPIV2Service::get('/wx/room/problem/group', []);
        return $this->resObjectGet($result, 'WxMessage', $request->path());
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
     * create
     * 微信公众号消息存储
     *
     * @param urls
     * @param start_time
     * @param name
     * @param content
     * @return array
     */
    public function officialMessageCreate(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            "urls" => "required|array",
            "name" => "required|string",
            "content" => "required|string",
        ]);
        $weiXinMessage = new WxOfficialMessage;
        $weiXinMessage->urls = json_encode($params['urls']);
        $weiXinMessage->start_time = date("Y-m-d H:i:s");
        $weiXinMessage->name = $params['name'];
        $weiXinMessage->content = $params['content'];
        try {
            $weiXinMessage->save();
        } catch (\Exception $e) {
            Log::debug('[WeiXinMessageController create] error message = ' . $e->getMessage());
            return $this->resObjectGet(false, 'WeiXinMessage', $request->path());
        }
        return $this->resObjectGet(true, 'WeiXinMessage', $request->path());
    }

}
