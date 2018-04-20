<?php

namespace App\Console\Commands;

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
        while (1) {
            try {
                $data = Redis::connection('queue')->rpop('crawl_image_queue');
//                $data = '{"id":1,"resource_url":"https://cdn.jin10.com/pic/67/276f4228e7cca4381fa575a7d8cbed6c.jpg","is_test":true}';
                Log::debug('[jinse::image:crawl] get pop from image queue ' . json_encode($data));
                $imageService = new ImageService();
                if (!empty($data)) {
                    $data = json_decode($data, true);
                    $imageRes = [];
                    $data['resource_url'] = explode(',', $data['resource_url']);

                    if (count($data['resource_url'])) {
                        foreach ($data['resource_url'] as $imageUrl) {
                            $imageItem = $imageService->uploadByImageUrl($imageUrl);
                            $imageItem = array_only($imageItem, ['oss_url', 'width', 'height', 'ext', 'mime_type']);
                            $imageRes[] = $imageItem;
                        }
                    }

                    if (count($imageRes)) {
                        $params['images'] = json_encode($imageRes, JSON_UNESCAPED_UNICODE);
                        $params['id'] = $data['id'];
                        DB::beginTransaction();
                        $result = [];
                        if ($data['is_test']) { // is_test 为真，将结果存入测试结果队列
                            $result = InternalAPIService::post('/item/test_result/image', $params);
                            if ($result['counter'] < 1 && $result['status'] == 4) { // 判断是否成功
                                InternalAPIService::post('/item/status/test_success', ['id' => $result['id']]);
                            }
                        } else { // 否则，存入结果队列
                            InternalAPIService::post('/item/result/image', $params);
                        }


                        DB::commit();
                    }
                } else {
                    sleep(5);
                }
            } catch (\Exception $e) {
                DB::rollBack();
            }
        }
    }
}
