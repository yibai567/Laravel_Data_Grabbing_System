<?php

namespace App\Listeners;

use App\Events\SaveAlarmResult;
use App\Models\V2\AlarmResult;
use App\Services\AMQPService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Log;

class SaveAlarmResultListener implements ShouldQueue
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
     * @param  SaveAlarmResult  $event
     * @return void
     */
    public function handle(SaveAlarmResult $event)
    {
        Log::debug('[SaveAlarmResultListener handle] ------- start -------');
        $data = $event->messages;
        if (empty($data)) {
            Log::debug('[SaveAlarmResultListener handle] data is not exists');
            return true;
        }

        $alarmResult = AlarmResult::find($data['alarm_result_id']);

        if (empty($alarmResult)) {
            Log::debug('[SaveAlarmResultListener handle] $alarmResult is empty');
            return true;
        }

        $type = $alarmResult->type;
        if ($type == AlarmResult::TYPE_WEWORK) {
            $queue = 'alarm_wework';
        } elseif ($type == AlarmResult::TYPE_PHONE) {
            $queue = 'alarm_sms';
        } else {
            Log::debug('[SaveAlarmResultListener handle] type is error');
            return true;
        }
        $option = [
            'server' => [
                'vhost' => 'crawl',
            ],
            'type' => 'direct',
            'exchange' => 'alarm',
            'queue' => $queue,
            'name' => 'SaveAlarmResultListener'
        ];

        try {
            $rmq = AMQPService::getInstance($option);
            $rmq->prepareExchange();
            $rmq->prepareQueue();
            $rmq->queueBind();
            $params = [
                'alarm_result_id' => $data['alarm_result_id'],
            ];
            $rmq->publishFormart($params, $queue);
            $rmq->close();
        } catch (\Exception $e) {
            Log::error('[SaveAlarmResultListener handle error]:'."\t".$e->getCode()."\t".$e->getMessage());
        }

        Log::debug('[SaveAlarmResultListener handle] ------- end -------');
        return true;

    }
}