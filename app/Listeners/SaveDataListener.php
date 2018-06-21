<?php

namespace App\Listeners;

use App\Events\SaveDataEvent;
use App\Models\V2\Project;
use App\Models\V2\TaskProjectMap;
use App\Models\V2\Task;
use App\AMQP;
use Illuminate\Contracts\Queue\ShouldQueue;
use Log;

class SaveDataListener implements ShouldQueue
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
     * @param  SaveDataEvent  $event
     * @return void
     */
    public function handle(SaveDataEvent $event)
    {
        Log::debug('[SaveDataListener handle] ------- start -------');
        $data = $event->messages;
        if (empty($data)) {
            Log::debug('[SaveDataListener handle] data is not exists');
            return true;
        }

        $taskProjectMap = TaskProjectMap::select('project_id')
                    ->where('task_id', $data['task_id'])
                    ->get()
                    ->toArray();

        if (empty($taskProjectMap)) {
            return true;
        }


        $projectIds = array_pluck($taskProjectMap, 'project_id');
        $projects = Project::whereIn('id', $projectIds)->get()->toArray();

        foreach ($projects as $project) {
            $type = '';
            switch ($project['exchange_type']) {
                case Project::EXCHANGE_TYPE_DIRECT :
                    $type = 'direct';
                    break;
                case Project::EXCHANGE_TYPE_FANOUT :
                    $type = 'fanout';
                    break;
                case Project::EXCHANGE_TYPE_ROUTE :
                    $type = 'route';
                    break;
                default:
                    break;
            }

            if (empty($type) || empty($project['vhost']) || empty($project['exchange']) || empty($project['queue'])) {
                Log::debug('[SaveDataListener Project error] project id ' . $project['id']);
                continue;
            }

            $option = [
                'server' => [
                    'vhost' => $project['vhost'],
                ],
                'type' => $type,
                'exchange' => $project['exchange'],
                'queue' => $project['queue']
            ];

            try {
                $rmq = AMQP::getInstance($option);
                $rmq->prepareExchange();
                $rmq->prepareQueue();
                $rmq->queueBind();
                foreach ($data['data'] as $value) {
                    $rmq->publish(json_encode($value), $project['queue']);
                }
                // $rmq->close();
            } catch (\Exception $e) {
                Log::error('[SaveDataListener handle error]:'."\t".$e->getCode()."\t".$e->getMessage());
            }
        }
        Log::debug('[SaveDataListener handle] ------- end -------');
        return true;
    }
}
