<?php

namespace App\Http\Controllers\WWW;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ValidatorService;
use App\Services\HttpService;
use Log;
use DB;
use App\Models\V2\WxMessage;

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

    /**
     * index
     * 首页显示
     *
     * @return array
     */
    public function newIndex(Request $request)
    {
        $data = [];
        $httpService = new HttpService();
        $wxMessageGroup = $httpService->get(config('url.jinse_open_url') . '/v2/wx/room/problem/group');
        $data = json_decode($wxMessageGroup->getContents(), true);
        $data['nav_status'] = 'new_wx_message';
        return view('jinse.new_index', ["data" => $data]);
    }

    /**
     * index
     * 首页显示
     *
     * @return array
     */
    public function ajaxMessageList(Request $request, $id)
    {
        $httpService = new HttpService();
        $wxMessageGroup = $httpService->get(config('url.jinse_open_url') . '/v2/wx/room/message/' . $id);
        echo $wxMessageGroup->getContents();
    }

    /**
     * downloadMessageList
     * 下载聊天记录文件
     *
     * @return array
     */
    public function downloadMessageList(Request $request, $id)
    {
        $httpService = new HttpService();
        $wxMessageGroup = $httpService->get(config('url.jinse_open_url') . '/v2/wx/room/message/' . $id);
        $messageList = $wxMessageGroup->getContents();
        $message = json_decode($messageList, true);
        if ($message['status_code'] != 200) {
            return false;
        }
        if (count($message['data']) > 0) {
            foreach ($message['data'] as $key => $value) {
                echo '<hr />';
                echo '发送人：' . $key .'<br>';
                foreach ($value as $content) {
                    echo $content['content'] .'</br>';
                }
            }
            echo '<hr />';
        }
    }

    public function text(Request $request)
    {
        $ask = WxMessage::where('contact_name', 'zoe 李雯 金色财经')->get();
        $askCount = count($ask);
        if ($askCount > 0) {
            $ask = $ask->toArray();
            $i = 1;
            if ($i == count($ask)) {
                return false;
            }
            foreach ($ask as $value) {

                echo '<hr />';
                echo '问题' . $i . '：' . strip_tags($value['content']) . "</br>";
                $nextId = '9999';
                if ($i < $askCount) {
                    $nextId = $ask[$i]['id'];
                }
                $answer = WxMessage::where('id', '>', $value['id'])
                        ->where('id', '<', $nextId)
                        ->orderBy('created_at', 'asc')
                        ->get();
                $data = [];
                if (count($answer) > 0) {
                    $answer = $answer->toArray();
                    $contactNames = array_unique(array_pluck($answer, 'contact_name'));
                    foreach ($answer as $key => $value) {
                        if (in_array($value['contact_name'], $contactNames)) {
                            $data[$value['contact_name']][] = $value;
                        }
                    }
                }
                if (count($data) > 0) {
                    foreach ($data as $key => $value) {
                        echo '<br />发送人：' . $key .'<br>';
                        foreach ($value as $content) {
                            $content = preg_replace("/\&lt;msg&gt;(.*?)\/msg&gt;/si", "", $content['content']);
                            echo strip_tags($content) .'</br>';
                        }
                    }
                }
                $i++;
            }
        }
    }
}
