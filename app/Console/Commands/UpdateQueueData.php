<?php

namespace App\Console\Commands;

use App\Models\Item;
use App\Models\QueueInfo;
use App\Services\APIService;
use App\Services\InternalAPIService;
use Illuminate\Console\Command;

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
        $this->info('jinse:update:queue_data ended！');
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
            if (!strpos($queueInfo->name, 'test')) {
                if ($length > 0 && $queueInfo == QueueInfo::IS_CAPTURE_IMAGE_FALSE) {
                    continue;
                }

                $jobs = Item::where('status', Item::STATUS_START)
                    ->where( function ($query) use ($queueInfo) {
                        if ($queueInfo->is_capture_image == QueueInfo::IS_CAPTURE_IMAGE_TRUE) {
                            $query->where('is_capture_image', $queueInfo->is_capture_image)->where('action_type', Item::TYPE_SYS);
                        } else {
                            $query->where('is_capture_image', $queueInfo->is_capture_image)->where('action_type', Item::TYPE_OUT);
                        }
                    })
                    ->where('is_proxy', $queueInfo->is_proxy)
                    ->where('data_type', $queueInfo->data_type)
                    ->pluck('id');

                if (empty($jobs)) {
                    continue;
                }

                foreach ($jobs as $job) {
                    $params = ['id' => $queueInfo->id, 'item_id' => $job];
                    InternalAPIService::post('queue_info/job', $params);
                    if ($queueInfo->is_capture_image == QueueInfo::IS_CAPTURE_IMAGE_TRUE) {
                        Item::update(['id' => $job, 'status' => Item::STATUS_STOP]);
                    }
                }
            }
        }
    }
}
