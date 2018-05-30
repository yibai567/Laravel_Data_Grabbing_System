<?php

namespace App\Services;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue;

class RabbitMQService extends Service
{
    protected $connection;
    protected $channel;

    public function __construct()
    {
        $this->__init();
    }

    /**
     * init
     * 初始化 RMQ结构
     */
    private function __init()
    {
        $this->connection = new AMQPStreamConnection(config('rabbitmq.host'), config('rabbitmq.port'), config('rabbitmq.login'), config('rabbitmq.password'), config('rabbitmq.vhost'));
        $this->channel = $this->connection->channel();

        // 声明交换机
        $this->channel->exchange_declare('save_result', 'fanout', false, true, false);
        $this->channel->exchange_declare('image', 'direct', false, true, false);
        $this->channel->exchange_declare('instant_task', 'direct', false, true, false);
        $this->channel->exchange_declare('error', 'fanout', false, true, false);

        // 声明队列
        $this->channel->queue_declare('result_img_processing', false, true, false, false);
        $this->channel->queue_declare('result_report', false, true, false, false);
        $this->channel->queue_declare('result_next_script', false, true, false, false);

        $this->channel->queue_declare('img_get_url', false, true, false, false);
        $this->channel->queue_declare('img_download', false, true, false, false);
        $this->channel->queue_declare('img_replace', false, true, false, false);

        $this->channel->queue_declare('engine_casperjs', false, true, false, false);
        $this->channel->queue_declare('engine_node', false, true, false, false);
        $this->channel->queue_declare('engine_chromeless', false, true, false, false);

        $this->channel->queue_declare('error_msg', false, true, false, false);

        // 队列和交换器绑定
        $this->channel->queue_bind('result_img_processing', 'save_result');
        $this->channel->queue_bind('result_report', 'save_result');
        $this->channel->queue_bind('result_next_script', 'save_result');

        $this->channel->queue_bind('img_get_url', 'image', 'get_url');
        $this->channel->queue_bind('img_download', 'image', 'download');
        $this->channel->queue_bind('img_replace', 'image', 'replace');

        $this->channel->queue_bind('engine_casperjs', 'instant_task', 'casperjs');
        $this->channel->queue_bind('engine_node', 'instant_task', 'node');
        $this->channel->queue_bind('engine_chromeless', 'instant_task', 'chromeless');

        $this->channel->queue_bind('error_msg', 'error');
    }

    /**
     * create
     * 创建队列
     * @param $exchange 当前交换机
     * @param $routingKey 当前关键词
     * @param $message 消息内容
     * @param $headers array 透传信息
     */
    public function create($exchange, $routingKey, $message, $headers = [])
    {

        \Log::debug('[RabbitMQService create] start');

        if (!is_array($headers)) {
            throw new \Exception('headers is not array');
        }

        $properties = [
            'content_type' => 'application/json',
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
        ];

        $initHeader = [
            'vhost' => config('rabbitmq.vhost'),
            'exchange' => $exchange,
            'routing_key' => $routingKey
        ];

        $messageBody['header'] = array_merge($initHeader, $headers);
        $messageBody['body'] = $message;

        \Log::debug('[RabbitMQService create] $messageBody = ', $messageBody);
        $AMPQMessage = new AMQPMessage(json_encode($messageBody), $properties);

        // 发送消息
        $this->channel->basic_publish($AMPQMessage, $exchange, $routingKey);

        $this->channel->close();
        $this->connection->close();
    }


    /**
     * 取出队列
     * @param $queue 队列名
     * @return array
     */
    public function get($queue)
    {
        $message = $this->channel->basic_get($queue);
        $result = [];
        if ($message->body) {
            $result = json_decode($message->body, true);
        }
        return $result;
    }

    /**
     * 确认处理完成
     */
    public function ack($tag = 1)
    {
        $this->channel->basic_ack($tag);
        $this->channel->close();
        $this->connection->close();
    }

    /**
     * 确认未处理完成
     */
    public function nack($tag = 1)
    {
        $this->channel->basic_nack($tag);
        $this->channel->close();
        $this->connection->close();
    }

    /**
     * 创建消费者
     * @param $queue 队列名
     * @return 回调函数
     */
    public function consume($queue, $callback)
    {
        $i = 1;
        $this->channel->basic_qos(null, 1, null);
        $this->channel->basic_consume($queue, '', false, false, false, false, $callback);
        while(count($this->channel->callbacks)) {
            if ($i > 100) {
                exit();
            }
            $this->channel->wait();
            $i++;
        }
        $this->channel->close();
        $this->connection->close();
    }


    /**
     * errorMsg
     * 创建错误信息
     * @param $message 消息内容
     */
    public function errorMsg($body, $msg = '')
    {
        $message['raw'] = $body;
        $message['msg'] = $msg;
        \Log::debug($msg);
        $this->create('error', '', $message);
    }
}
