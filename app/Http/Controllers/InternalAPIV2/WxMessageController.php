<?php
/**
 * WxMessageController
 * 微信消息处理
 * @author huangxingxing@jinse.com
 * @version 1.0
 * Date: 2018/06/26
 */

namespace App\Http\Controllers\InternalAPIV2;

use App\Models\V2\WxMessage;
use App\Models\V2\GroupWxMessage;
use Log;
use App\Services\ValidatorService;
use Illuminate\Http\Request;

class WxMessageController extends Controller
{
    /**
     * createGroupWxMessage
     * 创建微信信息分组
     *
     * @param
     * @return array
     */
    public function createGroup(Request $request)
    {
        $params = $request->all();
        //验证参数
        ValidatorService::check($params, [
            'date_time' => 'nullable|string|date',
        ]);

        if (empty($params['date_time'])) {
            $params['date_time'] = date('Y-m-d H:i:s');
        }

        try {
            $dateTime = strtotime($params['date_time']);
            $year = date('Y', $dateTime);
            $month = date('m', $dateTime);
            $day = date('d', $dateTime);
            $hour = date('H', $dateTime);
            $minutes = substr(date('i', $dateTime), 0, 1);
            $res = GroupWxMessage::where('year', $year)
                                            ->where('month', $month)
                                            ->where('day', $day)
                                            ->where('hour', $hour)
                                            ->where('minutes', $minutes)
                                            ->first();
            if (!empty($res)) {
                $result = $res->toArray();
                return $this->resObjectGet($result, 'WxMessage', $request->path());
            }

            $groupWxMessage = new GroupWxMessage;
            $groupWxMessage->year = $year;
            $groupWxMessage->month = $month;
            $groupWxMessage->day = $day;
            $groupWxMessage->hour = $hour;
            $groupWxMessage->minutes = $minutes;
            switch ($minutes) {
                case '0':
                    $groupWxMessage->times = '00-10';
                    break;
                case '1':
                    $groupWxMessage->times = '10-20';
                    break;
                case '2':
                    $groupWxMessage->times = '20-30';
                    break;
                case '3':
                    $groupWxMessage->times = '30-40';
                    break;
                case '4':
                    $groupWxMessage->times = '40-50';
                    break;
                case '5':
                    $groupWxMessage->times = '50-60';
                    break;
                default:
                    break;
            }
            $groupWxMessage->status = 1;
            $groupWxMessage->save();
        } catch (\Exception $e) {
            throw new \Dingo\Api\Exception\ResourceException("groupWxMessage create fail");
        }

        $result = $groupWxMessage->toArray();

        return $this->resObjectGet($result, 'WxMessage', $request->path());

    }

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
            'group_wx_message_id' => 'required|integer',
            'contact_name' => 'required|string|max:100',
            'room_name' => 'required|string|max:100',
            'content' => 'required|string|max:1000',
        ]);

        try {
            $wxMessage = new WxMessage;
            $wxMessage->group_wx_message_id = $params['group_wx_message_id'];
            $wxMessage->contact_name = $params['contact_name'];
            $wxMessage->room_name = $params['room_name'];
            $wxMessage->content = $params['content'];
            $wxMessage->status = 1;
            $wxMessage->save();

            $groupWxMessage = GroupWxMessage::find($params['group_wx_message_id']);
            $groupWxMessage->last_message = $params['content'];
            $groupWxMessage->save();

        } catch (\Exception $e) {
            throw new \Dingo\Api\Exception\ResourceException("wxMessage create fail");
        }

        $result = $wxMessage->toArray();

        return $this->resObjectGet($result, 'WxMessage', $request->path());
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
            $groupWxMessage = GroupWxMessage::find($params['id']);

            if (empty($groupWxMessage)) {
                throw new \Dingo\Api\Exception\ResourceException("groupWxMessage empty");
            }
            $groupWxMessage->status = 2;
            $groupWxMessage->save();

        } catch (\Exception $e) {
            throw new \Dingo\Api\Exception\ResourceException("groupWxMessage update fail");
        }

        $result = $groupWxMessage->toArray();

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
            $wxMessage = WxMessage::find($params['id']);

            if (empty($wxMessage)) {
                throw new \Dingo\Api\Exception\ResourceException("wxMessage empty");
            }
            $wxMessage->status = 2;
            $wxMessage->save();

        } catch (\Exception $e) {
            throw new \Dingo\Api\Exception\ResourceException("wxMessage update fail");
        }

        $result = $wxMessage->toArray();

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

        if (empty($params['offset'])) {
            $params['offset'] = 0;
        }

        if (empty($params['limit'])) {
            $params['limit'] = 1000;
        }

        if (empty($params['order'])) {
            $params['order'] = 'created_at';
        }

        if (empty($params['sort'])) {
            $params['sort'] = 'desc';
        }

        $startTime = time();
        $endTime = time() - 24*3600;

        $result = GroupWxMessage::where('created_at', '<=', date("Y-m-d H:i:s", $startTime));
        $result->where('created_at', '>=', date("Y-m-d H:i:s", $endTime));

        if (!empty($params['status'])) {
            $result->where('status', $params['status']);
        }

        $result->take($params['limit']);
        $result->skip($params['offset']);
        $result->orderBy($params['order'], $params['sort']);

        $res = $result->get();

        $data = [];
        if (!empty($result)) {
            $data = $res->toArray();
        }
        return $this->resObjectGet($data, 'WxMessage', $request->path());
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

        // if (empty($params['offset'])) {
        //     $params['offset'] = 0;
        // }

        // if (empty($params['limit'])) {
        //     $params['limit'] = 1000;
        // }

        if (empty($params['order'])) {
            $params['order'] = 'created_at';
        }

        if (empty($params['sort'])) {
            $params['sort'] = 'desc';
        }

        $groupWxMessage = GroupWxMessage::find($params['group_wx_message_id']);
        if (empty($groupWxMessage)) {
            throw new \Dingo\Api\Exception\ResourceException("groupWxMessage empty");
        }
        $groupWxMessageArr = $groupWxMessage->toArray();
        $result = WxMessage::where('group_wx_message_id', $params['group_wx_message_id']);

        if (!empty($params['status'])) {
            $result->where('status', $params['status']);
        }

        // $result->take($params['limit']);
        // $result->skip($params['offset']);
        $result->orderBy($params['order'], $params['sort']);

        $res = $result->get();

        $newWxMessage = [];
        if (!empty($result)) {
            $newWxMessage = $res->toArray();
        }
        $data['wx_message'] = $newWxMessage;
        $data['group_wx_message'] = $groupWxMessageArr;
        return $this->resObjectGet($data, 'WxMessage', $request->path());
    }
}