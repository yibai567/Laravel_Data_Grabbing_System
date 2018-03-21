<?php

namespace App\Listeners;

use App\Events\TaskPreview;
use App\Models\CrawlTask;
use App\Services\APIService;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class TaskPreviewListener
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
     * @param  TaskPreview  $event
     * @return void
     */
    public function handle(TaskPreview $event)
    {
        $params['id'] = $event->id;
        $data = APIService::internalPost('/internal/crawl/task/test', $params);
        if ($data['status_code'] !== 200) {
            errorLog($data['message'], $data['status_code']);
            return response()->json($data);
        }

    }
}
