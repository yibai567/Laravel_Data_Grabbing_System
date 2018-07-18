<?php
/**
 * AlarmResultController
 * 警报发送记录控制器
 * @author liqi@jinse.com
 * @version 1.0
 * Date: 2018/05/07
 */

namespace App\Http\Controllers\InternalAPIV2;

use App\Events\SaveAlarmResult;
use App\Models\V2\AlarmResult;
use App\Services\ValidatorService;
use App\Services\WeWorkService;
use iscms\Alisms\SendsmsPusher as Sms;
use Illuminate\Http\Request;
use Log;

class AlarmResultController extends Controller
{
    /**
     * create
     * 增加发送记录
     *
     * @param
     * @return boolean
     */
    public function create(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'type'    => 'required|integer|between:1,2',
            'content' => 'required|string|max:200',
            'phone'   => 'nullable|string|max:200',
            'wework'  => 'nullable|string|max:200',
        ]);

        if (empty($params['phone']) && empty($params['wework'])) {
            throw new \Dingo\Api\Exception\ResourceException('phone or wework is empty');
        }

        //已发送的,6小时内之内超过三次就不再发送
        $wheres = [];
        $wheres[] = ['type', $params['type']];
        $wheres[] = ['content', $params['content']];
        $wheres[] = ['status', AlarmResult::STATUS_SUCCESS];
        $wheres[] = ['send_at', '>', time() - 60 * 60 * 6];

        if (!empty($params['phone'])) {
            $wheres[] = ['phone', $params['phone']];
        }

        if (!empty($params['wework'])) {
            $wheres[] = ['wework', $params['wework']];
        }

        $alarmResult = AlarmResult::where($wheres)
                                ->get();
        $alarmResultNum = count($alarmResult);

        if ($alarmResultNum >= AlarmResult::MAX_SEND_NUM) {
            return $this->resObjectGet(true, 'alarm_result', $request->path());
        }

        try{
            $alarmResult = new AlarmResult();
            $alarmResult->type = $params['type'];
            $alarmResult->content = $params['content'];
            $alarmResult->phone = $params['phone'];
            $alarmResult->wework = $params['wework'];
            $alarmResult->status = AlarmResult::STATUS_SEND;

            $alarmResult->save();
            $data = ['alarm_result_id' => $alarmResult->id];
            //TODO 短信发送暂未开发
            if ($alarmResult->type == AlarmResult::TYPE_WEWORK) {
                event(new SaveAlarmResult($data));
            }

        } catch (\Exception $e) {
            Log::debug('[InternalAPIv2 AlarmResultController create] error message = ' . $e->getMessage());
        }

        return $this->resObjectGet(true, 'alarm_result', $request->path());

    }

    public function sendSms(Request $request, Sms $sms)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'alarm_result_id'    => 'required|integer|max:999999999',
        ]);

//        $alarmResult = AlarmResult::find($params['alarm_result_id']);
//
//        $users = $alarmResult->phone;
//
//        $result=$sms->send($users, ['code'=>1],"SMS_55110001");
        return $this->resObjectGet(true, 'alarm_result', $request->path());
    }

    public function sendWeWork(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            'alarm_result_id'    => 'required|integer|max:999999999',
        ]);

        $alarmResult = AlarmResult::find($params['alarm_result_id']);

        if (empty($alarmResult)) {
            Log::debug('[InternalAPIv2 AlarmResultController sendWeWork] $alarmResult is not found,alarm_result_id = ' . $params['alarm_result_id'] );
            return $this->resObjectGet(false, 'alarm_result', $request->path());
        }

        $users = str_replace(',', '|', $alarmResult->wework);

        try {
            $alarmResult->send_at = time();
            $alarmResult->save();

            $weWork = new WeWorkService();
            $result = $weWork->pushMessage('text', $alarmResult->content, $users);
            $result = json_decode($result, true);
            $alarmResult->status = AlarmResult::STATUS_SUCCESS;
            if ($result['errcode'] !== 0) {
                $alarmResult->status = AlarmResult::STATUS_FAIL;
            }

            $alarmResult->save();

        } catch (\Exception $e) {
            Log::debug('[InternalAPIv2 AlarmResultController sendWeWork]  企业微信发送通知异常,message = ' . $e->getMessage());
            $alarmResult->status = AlarmResult::STATUS_FAIL;
            $alarmResult->save();

            return $this->resObjectGet(false, 'alarm_result', $request->path());
        }

        return $this->resObjectGet(true, 'alarm_result', $request->path());

    }
}