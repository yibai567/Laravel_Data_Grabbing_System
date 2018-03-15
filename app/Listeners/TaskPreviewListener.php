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
        $crawlTask = CrawlTask::with('setting')->find($crawlTaskId);
        $output = 'test fail!';

        if ($crawlTask->cron_type == 2) {
            $folder = '/everyMinute/1';
        } elseif($crawlTask->cron_type == 3) {
            $folder = '/everyFiveMinutes/1';
        } elseif($crawlTask->cron_type == 4) {
            $folder = '/everyTenMinutes/1';
        } elseif($crawlTask->cron_type == 5) {
            $folder = '/everyThirtyMinutes/1';
        } else {
            $folder = '/keep';
        }

        $file = 'http_tmp_all_a.js';

        $scriptFile = config('path.jinse_script_path') . $folder . '/' . $file;
        $status = CrawlTask::IS_TEST_ERROR;
        if (file_exists($scriptFile) && $crawlTask->status !== CrawlTask::IS_START_UP) {
            $command = 'casperjs ' . $scriptFile . ' --taskid=' . $crawlTaskId;
            exec($command, $output, $returnvar);
            if ($returnvar == 0) {
                $status = CrawlTask::IS_TEST_SUCCESS;
            }
        }
        if (is_array($output)) {
            $output = json_encode($output);
        }
        $crawlTask->test_time = date('Y-m-d H:i:s');
        $crawlTask->status = $status;
        $crawlTask->test_result = $output;
        $crawlTask->save();    
    }
}
