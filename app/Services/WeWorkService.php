<?php
/**
 * Created by PhpStorm.
 * User: Pascal
 * Date: 2018/3/14
 * Time: 上午10:23
 */

namespace App\Services;


class WeWorkService extends Service
{
    const BASE_URI = 'https://qyapi.weixin.qq.com/cgi-bin/';
    const TIME_OUT = 10.0;

    private $corpId = null;
    private $corpSecret = null;
    private $agentId = null;
    private $accessToken = null;

    public function __construct()
    {
        $this->corpId = config('wework.corp_id');
        $this->corpSecret = config('wework.corp_secret');
        $this->agentId = config('wework.corp_app_config.agent_id');

        if ($this->__checkAccessToken()) {
            $this->accessToken = session('wework_access_token');
        } else {
            $this->accessToken = $this->getAccessToken();
            $this->__setAccessToken($this->accessToken);
        }
    }

    public function index()
    {
//        $departments = $this->getDepartment();
//        $users = [];
//
//        foreach ($departments as $department) {
//            $userlist = $this->getUserByDepId($department['id']);
//            $users = array_merge($users, $userlist);
//        }
        $userId = 'yuwenbin@jinse.com';
        $content = '测试消息：node节点已满，请及时添加';
        if ($this->sendMsgToUser($userId, $content)) {
            return 'success';
        } else {
            return 'error';
        }
    }

    public function sendMsgToUser($userId, $content)
    {
        $toParty = '';
        $toTag = '';
        $safe = '';
        $toUser = $userId;
        $msgType = 'text';
        $content = $content;
        $agentId = config('wework.corpAppConfig.agentId');
        $res = $this->pushMessage(
            $agentId,
            $msgType,
            $content,
            $toUser,
            $toParty,
            $toTag,
            $safe
        );
        return $res;
    }

    /**
     * 获取access_token
     * 根据传入的agentId
     *
     * @param agentId
     * @return string
     */
    public function getAccessToken()
    {
        $url = self::BASE_URI . 'gettoken?corpid=' . $this->corpId . '&corpsecret=' . $this->corpSecret;
        $data = json_decode($this->http_get($url)["content"], true);
        dd($data);

        if (!empty($data['errcode'])) {
            throw new Exception($data['errmsg'], $data['errcode']);
        }

        return $data['access_token'];
    }

    /**
     * 向特定用户发送消息
     *
     * @param Request $request
     * @param $agentId
     * @return int
     */
    public function sendMsg(Request $request, $agentId)
    {
        $toParty = $request -> to_party;
        $toTag = $request -> to_tag;
        $safe = $request -> safe;
        $toUser = $request -> partyId;
        $msgType = $request -> msg_type;
        $content = $request -> content;

        $res = $this->pushMessage(
            $agentId,
            $msgType,
            $content,
            $toUser,
            $toParty,
            $toTag,
            $safe
        );

        return $res;
    }

    /**
     * 获取部门信息
     *
     * @param $instance
     */
    public function getDepartment($departmentId = null)
    {
        $url = "https://qyapi.weixin.qq.com/cgi-bin/department/list?access_token=" . $this->accessToken;
        if ($departmentId) {
            if ($departmentId > 0) {
                $url .= "&id=" . $departmentId;
            } else {
                return [];
            }
        }
        $data = json_decode($this->http_get($url)["content"], true);
        if ($data['errcode'] == 0) {
            return $data['department'];
        } else {
            return [];
        }
    }


    /**
     * 获取用户信息
     *
     * @param Request $request
     * @param null $userId
     * @return mixed|string
     */
    public function getUser($userId)
    {
        $url = "https://qyapi.weixin.qq.com/cgi-bin/user/get?access_token=" . $this->accessToken;
        if ($userId) {
            $url .= "&userid=" . $userId;
        } else {
            return '{"errcode":-1,"errmsg":"userId is invalid"}';
        }

        return $this->http_get($url)["content"];
    }

    /**
     * 根据部门id获取用户信息
     *
     * @param Request $request
     * @param $departmentId
     *
     * @return mixed|string
     */
    public function getUserByDepId($departmentId, $fetchChild = 1, $simple = 1){
        if ($departmentId > 0) {
            if ($simple == 1) {
                $interface = "simplelist";
            } else {
                $interface = "list";
            }
            $url = "https://qyapi.weixin.qq.com/cgi-bin/user/$interface?access_token={$this->accessToken}&department_id=$departmentId&fetch_child=$fetchChild";
            $data = json_decode($this->http_get($url)["content"], true);
            if ($data['errcode'] == 0) {
                return $data['userlist'];
            } else {
                return [];
            }
        } else {
            return [];
        }
    }


    /**
     * 企业主动向用户推送消息
     * 支持文本消息、图片消息、语音消息、视频消息、文件消息、文本卡片消息、图文消息等消息类型
     *
     * @param $agentid
     * @param $touser
     * @param string $toparty
     * @param string $msgtype
     * @param string $content
     * @return int
     */
    public function pushMessage($agentId, $msgType = 'text', $content = "", $toUser='', $toParty = '', $toTag='', $safe=0)
    {
        if ($this->accessToken) {
            if (empty($content)) {
                return 0;
            }

            $msg = [
                'touser'=>$toUser,
                'toparty'=>$toParty,
                'totag'=>$toTag,
                'msgtype'=>$msgType,
                'agentid'=>$agentId,
            ];

            switch ($msgType) {
                case 'text':
                    /**
                     * {
                    "content" : "你的快递已到，请携带工卡前往邮件中心领取。\n出发前可查看<a href=\"http://work.weixin.qq.com\">邮件中心视频实况</a>，聪明避开排队。"
                    }
                     */
                    $msg['text'] = [
                        'content' => $content
                    ];
                    $msg['safe'] = 0;
                    break;
                case 'image':
                    /**
                     * {
                    "media_id" : "MEDIA_ID"
                    },
                     */
                    $msg['image'] =['medis_id' => $content];
                    $msg['safe'] = 0;
                    break;
                case 'voice':
                    /**
                     *  {
                    "media_id" : "MEDIA_ID"
                    }
                     */
                    $msg['voice'] = $content;
                    break;
                case 'video':
                    /**
                     * {
                    "media_id" : "MEDIA_ID",
                    "title" : "Title",
                    "description" : "Description"
                    }
                     */
                    $msg['video'] = $content;
                    $msg['safe'] = 0;
                    break;
                case 'file':
                    /**
                     *  {
                    "media_id" : "1Yv-zXfHjSjU-7LH-GwtYqDGS-zz6w22KmWAT5COgP7o"
                    }
                     */
                    $msg['file'] = $content;
                    $msg['safe'] = 0;
                    break;
                case 'textcard':
                    /**
                     * {
                    "title" : "领奖通知",
                    "description" : "<div class=\"gray\">2016年9月26日</div> <div class=\"normal\">恭喜你抽中iPhone 7一台，领奖码：xxxx</div><div class=\"highlight\">请于2016年10月10日前联系行政同事领取</div>",
                    "url" : "URL",
                    "btntxt":"更多"
                    }
                     */
                    $msg['textcard'] = $content;
                    $msg['safe'] = 0;
                    break;
                case 'news':
                    /**
                     * {
                    "articles" : [
                    {
                    "title" : "中秋节礼品领取",
                    "description" : "今年中秋节公司有豪礼相送",
                    "url" : "URL",
                    "picurl" : "http://res.mail.qq.com/node/ww/wwopenmng/images/independent/doc/test_pic_msg1.png",
                    "btntxt":"更多"
                    }
                    ]
                    }
                     */
                    $msg['news'] = $content;
                    break;
                case 'mpnews':
                    /**
                     * {
                    "articles":[
                    {
                    "title": "Title",
                    "thumb_media_id": "MEDIA_ID",
                    "author": "Author",
                    "content_source_url": "URL",
                    "content": "Content",
                    "digest": "Digest description"
                    }
                    ]
                    }
                     */
                    $msg['mpnews'] = $content;
                    $msg['safe'] = 0;
                    break;
            }

            $url = "https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=" . $this->accessToken;
            return $this->http_post($url, $msg)["content"];
        } else {
            return 0;
        }

    }

    /**
     * GET 请求
     * @param string $url
     */
    protected function http_get($url)
    {
        $oCurl = curl_init();
        if(stripos($url,"https://")!==FALSE){
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($oCurl, CURLOPT_VERBOSE, 1);
        curl_setopt($oCurl, CURLOPT_HEADER, 1);

        // $sContent = curl_exec($oCurl);
        // $aStatus = curl_getinfo($oCurl);
        $sContent = $this->execCURL($oCurl);
        curl_close($oCurl);

        return $sContent;
    }
    /**
     * POST 请求
     * @param string $url
     * @param array $param
     * @param boolean $post_file 是否文件上传
     * @return string content
     */
    protected function http_post($url,$param,$post_file=false){
        $oCurl = curl_init();

        if(stripos($url,"https://")!==FALSE){
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        if(PHP_VERSION_ID >= 50500 && class_exists('\CURLFile')){
            $is_curlFile = true;
        }else {
            $is_curlFile = false;
            if (defined('CURLOPT_SAFE_UPLOAD')) {
                curl_setopt($oCurl, CURLOPT_SAFE_UPLOAD, false);
            }
        }

        if($post_file) {
            if($is_curlFile) {
                foreach ($param as $key => $val) {
                    if(isset($val["tmp_name"])){
                        $param[$key] = new \CURLFile(realpath($val["tmp_name"]),$val["type"],$val["name"]);
                    }else if(substr($val, 0, 1) == '@'){
                        $param[$key] = new \CURLFile(realpath(substr($val,1)));
                    }
                }
            }
            $strPOST = $param;
        }else{
            $strPOST = json_encode($param);
        }

        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($oCurl, CURLOPT_POST,true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS,$strPOST);
        curl_setopt($oCurl, CURLOPT_VERBOSE, 1);
        curl_setopt($oCurl, CURLOPT_HEADER, 1);

        // $sContent = curl_exec($oCurl);
        // $aStatus  = curl_getinfo($oCurl);

        $sContent = $this->execCURL($oCurl);
        curl_close($oCurl);

        return $sContent;
    }

    /**
     * 执行CURL请求，并封装返回对象
     */
    protected function execCURL($ch){
        $response = curl_exec($ch);
        $error    = curl_error($ch);
        $result   = array( 'header' => '',
            'content' => '',
            'curl_error' => '',
            'http_code' => '',
            'last_url' => '');

        if ($error != ""){
            $result['curl_error'] = $error;
            return $result;
        }

        $header_size = curl_getinfo($ch,CURLINFO_HEADER_SIZE);
        $result['header'] = str_replace(array("\r\n", "\r", "\n"), "<br/>", substr($response, 0, $header_size));
        $result['content'] = substr( $response, $header_size );
        $result['http_code'] = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        $result['last_url'] = curl_getinfo($ch,CURLINFO_EFFECTIVE_URL);
        $result["base_resp"] = array();
        $result["base_resp"]["ret"] = $result['http_code'] == 200 ? 0 : $result['http_code'];
        $result["base_resp"]["err_msg"] = $result['http_code'] == 200 ? "ok" : $result["curl_error"];

        return $result;
    }

    //给URL地址追加参数
    protected function appendParamter($url,$key,$value){
        return strrpos($url,"?",0) > -1 ? "$url&$key=$value" : "$url?$key=$value";
    }


    //根据应用ID获取应用配置
    protected function getConfigByAgentId($id){
        $config = [];
        $configs = config('wework');
        foreach ($configs['corpAppConfig'] as $item) {
            if($item['agentId'] == $id){
                $config = $item;
                break;
            }
        }

        return $config;
    }

    /**
     * 设置AccessToken
     * @param $accessToken
     */
    private function __setAccessToken($accessToken)
    {
        $expired_at = time() + 7200;
        session('wework_access_token', $this->accessToken);
        session('wework_access_token_expired_at', $expired_at);
    }

    /**
     * 判断accessToken是否过期
     * @return bool
     */
    private function __checkAccessToken()
    {
        if (session('wework_access_token') && session('wework_access_token_expired_at') >= time()) {
            return true;
        }
        return false;
    }

}