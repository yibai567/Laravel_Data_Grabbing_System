<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AMQPService;
use App\Services\InternalAPIV2Service;
use Exception;

class RMQStatisticsConsole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:statistics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '统计消费';

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
            $option = [
                'server' => [
                    'vhost' => 'crawl',
                ],
                'type' => 'direct',
                'exchange' => 'statistic',
                'queue' => 'statistics',
                'name' => 'statistics'
            ];

            $rmq = AMQPService::getInstance($option);
            $rmq->prepareExchange();
            $rmq->prepareQueue();
            $rmq->queueBind();
            $rmq->consume($option['statistics'], $this->callback());
        } catch (Exception $e) {
            throw $e;
        }
    }
    private function callback()
    {
        return function($msg) {
            $message = json_decode($msg->body, true);
            if (empty($message)) {
                return false;
            }
            if (!isset($message['body']['type']) || !isset($message['body']['data'])) {
                Log::debug('[statistic error] body 结构体格式错误');
                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                return false;
            }

            // 请求统计接口
            try {
                $result = InternalAPIV2Service::post('/statistics/converter', $message['body']);

            } catch (Exception $e) {
                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
            }
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
            return true;
        };
    }
}
