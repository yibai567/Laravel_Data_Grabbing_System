<?php

namespace App\Listeners;

use App\Events\ProjectResultEvent;
use App\Models\V2\ProjectResult;
use App\Models\V2\TaskFilterMap;
use App\Models\V2\TaskActionMap;
use App\Models\V2\Action;
use App\Services\AMQPService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Log;

class ProjectResultListener implements ShouldQueue
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
     * @param  ProjectResultEvent  $event
     * @return void
     */
    public function handle(ProjectResultEvent $event)
    {
        Log::debug('[ProjectResultListener handle] ------- start -------');
        $data = $event->messages;
        if (empty($data)) {
            Log::debug('[ProjectResultListener handle] data is not exists');
            return true;
        }
        Log::debug('[ProjectResultListener handle] $data = ' . json_encode($data));

        $projectResult = ProjectResult::select('project_id', 'task_id')
                    ->find($data['project_result_id']);
        if (empty($projectResult)) {
            return true;
        }
        $projectResult = $projectResult->toArray();
        $taskFilterMap = TaskFilterMap::where('task_id', $projectResult['task_id'])
                    ->where('project_id', $projectResult['project_id'])
                    ->get()
                    ->toArray();

        if (!empty($taskFilterMap)) {
            // TODO 过滤器队列
        }

        $taskActionMap = TaskActionMap::select('id', 'action_id')
                    ->where('task_id', $projectResult['task_id'])
                    ->where('project_id', $projectResult['project_id'])
                    ->get()
                    ->toArray();

        if (empty($taskActionMap)) {
            return true;
        }

        $actionIds = array_pluck($taskActionMap, 'action_id');
        $taskActionMapIds = array_pluck($taskActionMap, 'id', 'action_id');
        $actions = Action::whereIn('id', $actionIds)->get()->toArray();

        foreach ($actions as $action) {
            $type = '';
            switch ($action['exchange_type']) {
                case Action::EXCHANGE_TYPE_DIRECT :
                    $type = 'direct';
                    break;
                case Action::EXCHANGE_TYPE_FANOUT :
                    $type = 'fanout';
                    break;
                case Action::EXCHANGE_TYPE_ROUTE :
                    $type = 'route';
                    break;
                default:
                    break;
            }

            if (empty($type) || empty($action['vhost']) || empty($action['exchange']) || empty($action['queue'])) {
                Log::debug('[ProjectResultListener Acction error] action id ' . $action['id']);
                continue;
            }

            $option = [
                'server' => [
                    'vhost' => $action['vhost'],
                ],
                'type' => $type,
                'exchange' => $action['exchange'],
                'queue' => $action['queue'],
                'name' => 'ProjectResultListener_' . rand(100000, 999999)
            ];
            try {
                $task_action_map_id = '';
                if (isset($taskActionMapIds[$action['id']])) {
                    $task_action_map_id = $taskActionMapIds[$action['id']];
                }
                $rmq = AMQPService::getInstance($option);
                $rmq->prepareExchange();
                $rmq->prepareQueue();
                $rmq->queueBind();
                $params = [
                    'project_result_id' => $data['project_result_id'],
                    'task_action_map_id' => $task_action_map_id,
                ];
                $rmq->publishFormart($params, $project['queue']);
                $rmq->close();
            } catch (\Exception $e) {
                Log::error('[ProjectResultListener handle error]:'."\t".$e->getCode()."\t".$e->getMessage());
            }
        }
        Log::debug('[ProjectResultListener handle] ------- end -------');
        return true;
    }
}
