<?php

namespace App\Console\Commands;

use App\Models\Item;
use App\Models\ItemTestResult;
use App\Services\ImageService;
use App\Services\InternalAPIService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Log;

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
        Log::debug('[jinse::image:crawl] start');
        $i = 1;
        while ($i <= 100) {
            try {
                $data = Redis::connection('queue')->rpop('crawl_image_queue');
                $imageService = new ImageService();
                if (!empty($data)) {
                    $data = json_decode($data, true);
                    $imageRes = [];
                    $data['resource_url'] = explode(',', $data['resource_url']);

                    if (count($data['resource_url'])) {
                        foreach ($data['resource_url'] as $imageUrl) {
                            $proxy = ($data['is_proxy'] == Item::IS_PROXY_YES) ? true : false;
                            $imageItem = $imageService->uploadByImageUrl($imageUrl, [], $proxy);
                            $imageItem['url'] = $imageItem['oss_url'];
                            $imageRes[] = $imageItem;
                        }
                    }

                    if (count($imageRes)) {
                        $params['images'] = json_encode($imageRes, JSON_UNESCAPED_UNICODE);
                        $params['id'] = $data['id'];
                        $result = [];
                        if ($data['is_test']) { // is_test 为真，将结果存入测试结果队列
                            Log::debug('[jinse::image:crawl] /item/test_result/image', $params);
                            $result = InternalAPIService::post('/item/test_result/image', $params);
                        } else { // 否则，存入结果队列
                            Log::debug('[jinse::image:crawl] /item/result/image', $params);
                            InternalAPIService::post('/item/result/image', $params);
                        }
                    }
                } else {
                    sleep(5);
                }
            } catch (\Exception $e) {
                DB::rollBack();
            }
            $i++;
        }
    }
}
