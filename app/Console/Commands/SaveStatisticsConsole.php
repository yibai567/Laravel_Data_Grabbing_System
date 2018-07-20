<?php

namespace App\Console\Commands;

use App\Models\V2\Task;
use App\Models\V2\Data;
use App\Models\V2\TaskStatistics;
use App\Services\InternalAPIV2Service;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Log;

class SaveStatisticsConsole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jinse:save:statistics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'save statistics total_result';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $task = Task::get();
        foreach ($task as $key => $value) {
           $totalResult = Data::where('task_id', $value->id)->count();
           $taskStatistics = TaskStatistics::where('task_id', $value->id)->first();
           if (empty($taskStatistics)) {
                $taskStatistics = new TaskStatistics;
                $taskStatistics->task_id = $value->id;
           }
            $taskStatistics->data_type = $value->data_type;
            $taskStatistics->is_proxy = $value->is_proxy;
            $taskStatistics->cron_type = $value->cron_type;
            $taskStatistics->status = $value->status;
            $taskStatistics->total_result = $totalResult;
            $taskStatistics->last_job_at = date('Y-m-d H:i:s');
            $taskStatistics->save();
        }
        return true;
    }
}
