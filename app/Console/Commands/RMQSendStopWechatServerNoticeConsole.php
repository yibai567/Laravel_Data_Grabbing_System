<?php


namespace App\Console\Commands;

use App\Services\AMQPService;
use App\Services\InternalAPIV2Service;
use Illuminate\Console\Command;
use Log;

class RMQSendStopWechatServerNoticeConsole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:send_stop_wechat_server_notice {type}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '停止微信服务信息发送消费者';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $type = $this->argument('type');
            if (!in_array($type, [1,2])) {
                $this->error('{type} must is email or wework');
                return false;
            }

            if ($type == 1) {
                $queue = 'send_notice_email';
                $customer_path = '/wechat_server/send_email';
                $name = 'send_notice_email';
            } elseif ($type == 2) {
                $queue = 'send_notice_wework';
                $customer_path = '/wechat_server/send_wework';
                $name = 'send_notice_wework';
            } else {
                Log::debug('[RMQSendStopWechatServerNoticeConsole handle] type is error');
                return false;
            }

            $option = [
                'server' => [
                    'vhost' => 'crawl',
                ],
                'type' => 'direct',
                'exchange' => 'wechat',
                'queue' => $queue,
                'name' => $name
            ];


            $rmq = AMQPService::getInstance($option);
            $rmq->prepareExchange();
            $rmq->prepareQueue();
            $rmq->queueBind();
            $rmq->consume($queue, $this->callback($customer_path));

        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function callback($customerPath)
    {
        return function($msg) use ($customerPath) {
            $message = json_decode($msg->body, true);

            $result = InternalAPIV2Service::post($customerPath, $message['body']);
            if ($result) {
                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
            }
        };
    }
}