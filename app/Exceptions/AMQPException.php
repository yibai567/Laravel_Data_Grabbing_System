<?php
namespace App\Exceptions;


class AMQPException extends \Exception {

    /**
     * 配置文件错误
     */
    const CONFIG_NOT_VALID = 10001;

    /**
     * 实例初始化错误
     */
    const CLIENT_INIT_ERROR = 10002;

    /**
     * exchange初始化错误
     */
    const EXCHANGE_INIT_ERROR = 10003;

    /**
     * queue初始化错误
     */
    const QUEUE_INIT_ERROR = 10004;

    /**
     * queue绑定错误
     */
    const QUEUE_BIND_ERROR = 10005;

    /**
     * 发布消息失败
     */
    const MESSAGE_PUBLISH_ERROR = 10011;

    /**
     * 获取消息失败
     */
    const MESSAGE_GET_ERROR = 10012;

    /**
     * ack一条消息失败
     */
    const MESSAGE_ACK_ERROR = 10013;

    public function __construct($message, $code = 0) {

        if ($message instanceof Exception) {
            parent::__construct($message->getMessage(), intval($message->getCode()));
        } else {
            parent::__construct($message, intval($code));
        }
    }

}
