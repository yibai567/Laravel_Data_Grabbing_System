<?php

namespace App\Console\Commands;

use App\Models\V2\AlarmResult;
use App\Models\V2\Requirement;
use App\Services\InternalAPIV2Service;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RequirementAlarm extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'requirement:alarm';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitoring unprocessed requirements';

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
        $count = DB::table('t_requirement_pool')->where('status', Requirement::STATUS_TRUE)->count();

        if ($count > 0) {//存在未处理的需求
            $data = [
                'type' => AlarmResult::TYPE_WEWORK,
                'content' => '存在未处理的需求',
                'wework' => config('alarm.alarm_recipient')
            ];

            $result = InternalAPIV2Service::post('/alarm_result', $data);
            if (!$result) {
                Log::debug('[RequirementAlarm handle] create alarm_result is failed');
                return false;
            }
        }
        return true;
    }
}
