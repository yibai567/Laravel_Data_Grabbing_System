<?php

namespace App\Listeners;

use App\Events\WechatServerEvent;
use App\Services\AMQPService;
use App\Services\InternalAPIV2Service;
use Illuminate\Contracts\Queue\ShouldQueue;
use Log;

class WechatServerListener implements ShouldQueue
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
    public function handle(WechatServerEvent $event)
    {
        Log::debug('[WechatServerListener handle] ------- start -------');
        $data = $event->messages;
        if (empty($data)) {
            Log::debug('[WechatServerListener handle] data is not exists');
            return true;
        }
        Log::debug('[WechatServerListener handle] $data = ' . json_encode($data));

        $option = [
            'server' => [
                'vhost' => 'crawl',
            ],
            'type' => 'direct',
            'exchange' => 'wechat',
            'queue' => 'wx_server',
            'name' => 'wx_server'
        ];
        try {
            $rmq = AMQPService::getInstance($option);
            $rmq->prepareExchange();
            $rmq->prepareQueue();
            $rmq->queueBind();
            $rmq->publishFormart($data, $queue);
            $rmq->close();
        } catch (\Exception $e) {
            Log::error('[WechatServerListener handle error]:'."\t".$e->getCode()."\t".$e->getMessage());
        }
        Log::debug('WechatServerListener [handle] ------- end -------');
        return true;
    }
}
