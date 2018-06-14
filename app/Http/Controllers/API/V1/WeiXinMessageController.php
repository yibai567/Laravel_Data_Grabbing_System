<?php

namespace App\Http\Controllers\API\V1;

use Illuminate\Http\Request;
use Log;
use App\Services\ValidatorService;
use App\Models\WeiXinMessage;

/**
 * WeiXinMessageController
 * 微信公众号消息保存
 *
 * @author huangxingxing@jinse.com
 * @version 1.1
 * Date: 2018/06/7
 */
class WeiXinMessageController extends Controller
{

    /**
     * create
     * 任务创建
     *
     * @param urls
     * @param start_time
     * @param name
     * @param content
     * @return array
     */
    public function create(Request $request)
    {
        $params = $request->all();

        ValidatorService::check($params, [
            "urls" => "required|array",
            "start_time" => "required|date",
            "name" => "required|string",
            "content" => "required|string",
        ]);
        $weiXinMessage = new WeiXinMessage;
        $weiXinMessage->urls = json_encode($params['urls']);
        $weiXinMessage->start_time = $params['start_time'];
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
