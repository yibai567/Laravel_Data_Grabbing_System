<?php

namespace App\Listeners;

use App\Events\ConverterTaskEvent;
use App\Models\V2\Task;
use App\Services\AMQPService;
use App\Services\InternalAPIV2Service;
use Illuminate\Contracts\Queue\ShouldQueue;
use Log;

class ConverterTaskListener implements ShouldQueue
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
     * @param  ConverterTaskEvent  $event
     * @return void
     */
    public function handle(ConverterTaskEvent $event)
    {
        Log::debug('[ConverterTaskListener handle] ------- start -------');
        $data = $event->messages;
        if (empty($data)) {
            Log::debug('[ConverterTaskListener handle] data is not exists');
            return true;
        }
        Log::debug('[ConverterTaskListener handle] $data = ' . json_encode($data));

        $task = InternalAPIV2Service::get('/task', ['id' => $data['task_id']]);
        if (empty($task)) {
            return true;
        }

        switch ($task['data_type']) {
            case Task::DATA_TYPE_CASPERJS:
                $queue = 'engine_casperjs';
                break;

            case Task::DATA_TYPE_HTML:
                $queue = 'engine_node';
                break;

            case Task::DATA_TYPE_API:
                $queue = 'engine_node';
                break;

            default:
                $queue = '';
                break;
        }
        $taskRunLog = InternalAPIV2Service::post('/task_run_log', ['task_id' => $data['task_id']]);
        if (empty($taskRunLog)) {
            Log::debug('[ConverterTaskListener] task_run_log error');
            return true;
        }

        $params = [
            'path' => $task['script_path'],
            'task_run_log_id' => $taskRunLog['id'],
            'url' => $data['detail_url']
        ];

        $option = [
            'server' => [
                'vhost' => 'crawl',
            ],
            'type' => 'direct',
            'exchange' => 'instant_task',
            'queue' => $queue,
            'name' => 'ConverterTaskListener_' . $queue
        ];
        try {

            $rmq = AMQPService::getInstance($option);
            $rmq->prepareExchange();
            $rmq->prepareQueue();
            $rmq->queueBind();
            $rmq->publishFormart($params, $queue);
            $rmq->close();
        } catch (\Exception $e) {
            Log::error('[ConverterTaskListener handle error]:'."\t".$e->getCode()."\t".$e->getMessage());
        }
        Log::debug('[ConverterTaskListener handle] ------- end -------');
        return true;
    }
}
