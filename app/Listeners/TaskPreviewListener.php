<?php

namespace App\Listeners;

use App\Events\TaskPreview;
use App\Models\CrawlTask;
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
        $crawlTaskId = $event->id;
        $crawlTask = CrawlTask::find($crawlTaskId);

        $output = 'test fail!';
        $scriptFile = config('path.jinse_script_path') . '/list_a.js';
        $status = CrawlTask::IS_TEST_ERROR;
        if (file_exists($scriptFile) && $crawlTask->status !== CrawlTask::IS_START_UP) {
            $command = 'casperjs ' . $scriptFile . ' --env=test';
            exec($command, $output, $returnvar);
            if ($returnvar == 0) {
                $status = CrawlTask::IS_TEST_SUCCESS;
            }
        }
        $crawlTask->test_time = date('Y-m-d H:i:s');
        $crawlTask->status = $status;
        $crawlTask->test_result = $output;
        $crawlTask->save();    
    }
}
