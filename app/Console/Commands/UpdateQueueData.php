<?php

namespace App\Console\Commands;

use App\Models\Item;
use App\Models\QueueInfo;
use App\Services\APIService;
use App\Services\InternalAPIService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class UpdateQueueData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jinse:update:queue_data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '更新队列';

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
        $this->info('jinse:update:queue_data start！');
        $this->__updateQueueData();
        sleep(5);
    }

    /**
     * __updateQueueData
     * 更新队列逻辑
     */
    private function __updateQueueData()
    {
        $queueInfos = QueueInfo::all();
        foreach ($queueInfos as $queueInfo) {
            $length = Redis::connection($queueInfo->db)->lLen($queueInfo->name);

            //测试队列不更新
            if (!strpos($queueInfo->name, 'test') && $queueInfo->data_type !== Item::DATA_TYPE_CAPTURE) {
                //非截图任务队列，如果队列有值不更新
                if ($length > 0) {
                    continue;
                }

                $jobs = Item::where('status', Item::STATUS_START)
                    ->where('action_type', Item::TYPE_OUT)
                    ->where('is_proxy', $queueInfo->is_proxy)
                    ->where('data_type', $queueInfo->data_type)
                    ->pluck('id')
                    ->toArray();

                if (empty($jobs)) {
                    continue;
                }

                foreach ($jobs as $job) {
                    try {
                        $params = [
                            'id' => $queueInfo->id,
                            'item_id' => $job,
                        ];
                        InternalAPIService::post('/queue_info/job', $params);
                    } catch (\Exception $e) {
                        echo $e->getMessage();
                    }
                }
            }
        }
        InternalAPIService::get('/queue_info/update/current_lengths');
    }
}
