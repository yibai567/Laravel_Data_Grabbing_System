<?php

namespace App\Http\Controllers\WWW;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ValidatorService;
use App\Services\HttpService;
use Log;
use DB;

class WxMessageController extends Controller
{
    /**
     * index
     * 首页显示
     *
     * @return array
     */
    public function index(Request $request)
    {
        $params = $request->all();
        ValidatorService::check($params, [
            'offset' => 'nullable|integer',
            'limit' => 'nullable|integer',
            'order' => 'nullable|string',
            'sort' => 'nullable|string',
            'status' => 'nullable|integer'
        ]);

        $data = [];
        $httpService = new HttpService();
        $wxMessageGroup = $httpService->get(config('url.jinse_open_url') . '/v2/wx/message/group', $params);
        $data = json_decode($wxMessageGroup->getContents(), true);
        $data['status'] = $params['status'];
        $data['nav_status'] = 'wx_message';
        return view('jinse.index', ["data" => $data]);
    }

    public function ajaxWxMessageList(Request $request)
    {
        $params = $request->all();
        ValidatorService::check($params, [
            'group_wx_message_id' => 'required|integer',
            'offset' => 'nullable|integer',
            'limit' => 'nullable|integer',
            'order' => 'nullbale|string',
            'sort' => 'nullbale|nullbale',
            'status' => 'nullable|integer'
        ]);
        $data = [];
        $httpService = new HttpService();
        $wxMessage = $httpService->get(config('url.jinse_open_url') . '/v2/wx/message', $params);
        $wxMessage = json_decode($wxMessage->getContents(), true);

        $wxMessage['offset'] = $params['offset'];
        $wxMessage['limit'] = $params['limit'];
        $wxMessage['order'] = $params['order'];
        $wxMessage['sort'] = $params['sort'];

        echo json_encode($wxMessage);
    }

    public function ajaxUpdateGroupStatus(Request $request)
    {
        $params = $request->all();
        ValidatorService::check($params, [
            'id' => 'required|integer',
        ]);
        $data = [];
        $httpService = new HttpService();
        $wxMessage = $httpService->postV1(config('url.jinse_open_url') . '/v2/wx/message/group/status', $params);
        $wxMessage = json_decode($wxMessage->getContents(), true);
        echo json_encode($wxMessage);
    }

    public function ajaxUpdateStatus(Request $request)
    {
        $params = $request->all();
        ValidatorService::check($params, [
            'id' => 'required|integer',
        ]);
        $data = [];
        $httpService = new HttpService();
        $wxMessage = $httpService->postV1(config('url.jinse_open_url') . '/v2/wx/message/status', $params);
        $wxMessage = json_decode($wxMessage->getContents(), true);
        echo json_encode($wxMessage);
    }
}
