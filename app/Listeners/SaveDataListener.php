<?php

namespace App\Listeners;

use App\Events\SaveDataEvent;
use App\Models\V2\Project;
use App\Models\V2\Task;
use App\Models\V2\TaskProjectMap;
use App\Services\RabbitMQService;
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

        // 获取事件中的信息
        $datas = $event->datas;
        Log::debug('[SaveDataListener handle] datas: '.json_encode($datas, JSON_UNESCAPED_UNICODE));

        if (empty($datas)) {
            Log::debug('[SaveDataListener handle] data is not exists');

            return true;
        }

        //这批数据的来源同一个script
        $taskId = $datas[0]['task_id'];

        $taskProjectMap = TaskProjectMap::where('task_id',$taskId)->get(['project_id']);

        if (empty($taskProjectMap)) {
            Log::debug('[SaveDataListener handle] task_project_map is empty');

            return true;
        }

        try {
            foreach ($datas as $data) {

                foreach ($taskProjectMap['project_id'] as $projectId) {
                    //查询分发项目信息
                    $project = Project::find($projectId);
                    $exchange = $project->exchange;
                    $routingKey = $project->routing_key;

                    $rabbitMQ = new RabbitMQService();

                    //调用分发项目队列
                    $rabbitMQ->create($exchange, $routingKey, $data);

                }
            }

        } catch (\Exception $e) {
            // DB::rollBack();
            Log::error('[SaveDataListener handle error]:'."\t".$e->getCode()."\t".$e->getMessage());
        }

        Log::debug('[SaveDataListener handle] ------- end -------');

        return true;
    }
}
