<?php

namespace App\Listeners;

use App\Events\StatisticsEvent;
use App\Models\V2\Task;
use App\Services\AMQPService;
use App\Services\InternalAPIV2Service;
use Illuminate\Contracts\Queue\ShouldQueue;
use Log;

class StatisticsListener implements ShouldQueue
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
     * @param  StatisticsEvent  $event
     * @return void
     */
    public function handle(StatisticsEvent $event)
    {
        Log::debug('[StatisticsListener handle] ------- start -------');
        $data = $event->messages;
        if (empty($data)) {
            Log::debug('[StatisticsListener handle] data is not exists');
            return true;
        }
        Log::debug('[StatisticsListener handle] $data = ' . json_encode($data));

        $option = [
            'server' => [
                'vhost' => 'crawl',
            ],
            'type' => 'direct',
            'exchange' => 'statistic',
            'queue' => 'statistics',
            'name' => 'statistics'
        ];
        try {

            $rmq = AMQPService::getInstance($option);
            $rmq->prepareExchange();
            $rmq->prepareQueue();
            $rmq->queueBind();
            $rmq->publishFormart($data, $queue);
            $rmq->close();
        } catch (\Exception $e) {
            Log::error('[ConverterTaskListener handle error]:'."\t".$e->getCode()."\t".$e->getMessage());
        }
        Log::debug('[ConverterTaskListener handle] ------- end -------');
        return true;
    }
}
