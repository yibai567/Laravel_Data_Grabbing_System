<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use App\Models\Script;
use App\Models\TaskRunLog;
use App\Services\InternalAPIService;
use Illuminate\Support\Facades\Redis;
use Log;

class CreateScriptQueueConsole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jinse:create_script_queue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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

        $scriptList = $this->__getScriptList();
        if (empty($scriptList)) {
            $this->info("还没有已生成的脚本，稍请后重试");
            return false;
        }
        $queueList = [];
        foreach ($scriptList as $key => $value) {
            $driver = $this->__getDriver($value['languages_type'], $value['script_id']);
            $cron = $this->__getCron($value['cron_type'], $value['script_id']);
            $queueName = 'script_queue_' . $driver . '_' . $cron;
            $queueList[$queueName][] = [
                'path' => 'script_' . $value['script_id'] . '.js',
                'task_id' => $value['id'],
            ];
        }

        if (empty($queueList)) {
            $this->info("还没有已生成的脚本，请稍后重试");
            return false;
        }

        foreach ($queueList as $k => $v) {
            if (Redis::connection('queue')->lLen($k) <= 0 && $v > 0) {
                foreach($v as $item) {
                    $taskRunLog = InternalAPIService::post('/task_run_log', ['task_id' => $item['task_id']]);
                    if (empty($taskRunLog)) {
                        $this->info("task_run_log添加失败，请后重试");
                        continue;
                    }
                    $item['task_run_log_id'] = $taskRunLog['id'];
                    Redis::connection('queue')->lpush($k, json_encode($item));
                }
            } else {
                continue;
            }
        }
    }

    /**
     * __getScriptList
     * 获取已生成脚本列表
     */
    private function __getScriptList()
    {
        $data = [];
        Task::select(['id', 'languages_type', 'cron_type', 'script_id'])
                ->where('status', Task::STATUS_START)
                ->chunk(500, function ($script) use(&$data) {
                    if ($script) {
                        $script = $script->toArray();
                    } else {
                        return false;
                    }
                    $result = $script;
                    $data = array_merge_recursive($data, $result);
                    return true;
                });
        return $data;
    }

    private function __getDriver($languagesType, $scriptId)
    {
        switch ($languagesType) {
            case Script::LANGUAGES_TYPE_CASPERJS:
                $driver = 'casper';
                break;

            case Script::LANGUAGES_TYPE_HTML:
                $driver = 'node';
                break;

            case Script::LANGUAGES_TYPE_API:
                $driver = 'node';
                break;

            default:
                Log::error('script_id 为 [' . $scriptId . '] 的 languagesType 值不对 = ' . $languagesType);
                $driver = '';
                break;
            }
        return $driver;
    }

    private function __getCron($cronType, $scriptId)
    {

        switch ($cronType) {
            case Script::CRON_TYPE_KEEP;
                $cron = '1m';
                break;

            case Script::CRON_TYPE_EVERY_FIVE_MINUTES;
                $cron = '5m';
                break;

            case Script::CRON_TYPE_EVERY_TEN_MINUTES:
                $cron = '10m';
                break;

            default:
                Log::error('script_id 为 [' . $scriptId . '] 的 cron_type 值不对 = ' . $cronType);
                $cron = '';
                break;
            }
        return $cron;
    }
}
