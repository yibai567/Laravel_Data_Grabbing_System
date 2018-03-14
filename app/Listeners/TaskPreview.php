<?php

namespace App\Listeners;

use App\Models\CrawlTask;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class TaskPreview implements ShouldQueue
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
     * @param  object  $event
     * @return void
     */
    public function handle(\App\Events\TaskPreview $event)
    {
        $id = $event->id;
        $task = CrawlTask::find($id);
        // Todo
    }
}
