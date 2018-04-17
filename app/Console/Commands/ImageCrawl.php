<?php

namespace App\Console\Commands;

use App\Services\HttpService;
use App\Services\ImageService;
use App\Services\InternalAPIService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class ImageCrawl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jinse:image:crawl';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'crawl image from image queue';

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
        while (true) {
            try {
                $data = Redis::connection('queue')->rpop('crawl_image_queue');
                $imageService = new ImageService();
                if (!empty($data)) {
                    $data = json_decode($data, true);
                    $imageRes = [];
                    if (count($data['images'])) {
                        foreach ($data['images'] as $imageUrl) {
                            $imageItem = $imageService->uploadByImageUrl($imageUrl);
                            $imageItem = array_only($imageItem, ['oss_url', 'width', 'height', 'ext', 'mime_type']);
                            $imageItem['url'] = $imageItem['oss_url'];
                            $imageRes[] = $imageItem;
                        }
                    }

                    if (count($imageRes)) {
                        $params['images'] = $imageRes;
                        $params['id'] = $data['id'];
                        DB::beginTransaction();
                        if ($data['is_test']) { // is_test 为真，将结果存入测试结果队列
                            InternalAPIService::post('/item/test/result/update', $params);
                        } else { // 否则，存入结果队列
                            InternalAPIService::post('/item/result/update', $params);
                        }
                        DB::commit();
                    }
                }
            } catch (\Exception $e) {
                DB::rollBack();
                return null;
            }
        }
    }
}
