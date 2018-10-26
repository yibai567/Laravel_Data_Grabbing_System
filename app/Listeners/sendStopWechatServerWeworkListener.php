<?php

namespace App\Listeners;

use App\Events\StopWechatServerEvent;
use App\Events\WechatServerEvent;
use App\Services\AMQPService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Log;

class sendStopWechatServerWeworkListener implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  WechatServerEvent  $event
     * @return void
     */
    public function handle(StopWechatServerEvent $event)
    {
        Log::debug('[sendStopWechatServerWechatListener handle] ------- start -------');
        $data = $event->messages;
        if (empty($data)) {
            Log::debug('[sendStopWechatServerWechatListener handle] data is not exists');
            return true;
        }
        Log::debug('[sendStopWechatServerWechatListener handle] $data = ' . json_encode($data));

        $option = [
            'server' => [
                'vhost' => 'crawl',
            ],
            'type' => 'direct',
            'exchange' => 'wechat',
            'queue' => 'send_notice_wework',
            'name' => 'send_notice_wework'
        ];

        try {
            $rmq = AMQPService::getInstance($option);
            $rmq->prepareExchange();
            $rmq->prepareQueue();
            $rmq->queueBind();
            $rmq->publishFormart(['wechat_server_id'=>$data->id], $option['queue']);
            $rmq->close();
        } catch (\Exception $e) {
            Log::error('[WechatServerListener handle error]:'."\t".$e->getCode()."\t".$e->getMessage());
        }
        Log::debug('WechatServerListener [handle] ------- end -------');
        return true;
    }
}
