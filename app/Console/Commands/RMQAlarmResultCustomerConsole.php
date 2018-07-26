<?php

namespace App\Console\Commands;


use App\Models\V2\AlarmResult;
use App\Services\AMQPService;
use App\Services\InternalAPIV2Service;
use Illuminate\Console\Command;

class RMQAlarmResultCustomerConsole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:alarm_reulst_customer {type}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '警报发送消费者';

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
                $this->error('{type} must is phone or wework');
                return false;
            }

            $exchangeType = 'direct';

            if ($type == AlarmResult::TYPE_WEWORK) {
                $queue = 'alarm_wework';
                $customer_path = '/alarm_result/send_wework';
            } elseif ($type == AlarmResult::TYPE_PHONE) {
                $queue = 'alarm_sms';
                $customer_path = '/alarm_result/send_phone';
            } else {
                Log::debug('[RMQAlarmResultCustomerConsole handle] type is error');
                return false;
            }

            $option = [
                'server' => [
                    'vhost' => 'crawl',
                ],
                'type' => $exchangeType,
                'exchange' => 'alarm',
                'queue' => $queue,
                'name' => 'RMQAlarmResultCustomerConsole' . $type
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