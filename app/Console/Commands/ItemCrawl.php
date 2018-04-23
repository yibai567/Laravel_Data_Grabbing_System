<?php

namespace App\Console\Commands;

use Log;
use App\Services\RequestService;
use App\Services\InternalAPIService;
use App\Services\HttpService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use App\Models\QueueInfo;
use App\Models\Item;
use GuzzleHttp\Exception\RequestException;


class ItemCrawl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'item:crawl {queue_id} {is_test} {is_proxy}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'item crawl process';

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
        $queueId = $this->argument('queue_id');

        if (empty($queueId)) {
            echo "参数 queueId 不能为空 \n";
            return false;
        }

        $isTest = (int)$this->argument('is_test');

        if ($isTest != QueueInfo::TYPE_TEST && $isTest != QueueInfo::TYPE_PRO) {
            echo "参数 is_test 不能为空 值是1或2 \n";
            return false;
        }

        $isProxy = (int)$this->argument('is_proxy');

        if ($isProxy != Item::IS_PROXY_YES && $isProxy != Item::IS_PROXY_NO) {
            echo "参数 is_proxy 不能为空 值是1或2 \n";
            return false;
        }
        //根据队列id获取数据信息
        $httpService = new HttpService();

        $resQueue = $httpService->get(config('url.jinse_open_url') . '/v1/queue_info/job', ['id' => $queueId]);
        $queueInfo = json_decode($resQueue, true);
        if (empty($queueInfo['data'])) {
            echo "没有可处理的任务 \n";
            return false;
        }
        $this->__request($queueInfo['data'], $isTest, $isProxy);
    }

    /**
     * __getByQueueName
     * 根据队列名获取列表数据
     *
     * @return array
     */
    private function __getByQueueName($name)
    {
        return APIService::internalGet('/internal/crawl/task/queue/name?name=' . $name);
    }
    /**
     * __request
     * 抓取请求
     *
     * @return array
     */
    private function __request($params, $isTest, $isProxy)
    {
        $itemParams = $params;
        $item['item_run_log_id'] = $itemParams['item_run_log_id'];
        $item['start_time'] = date('Y-m-d H:i:s');
        $header = [];

        if (!empty($itemParams['header'])) {
            $header = json_decode($itemParams['header'], ture);
        }

        if ($isProxy == Item::IS_PROXY_YES) {
            $isProxy = true;
        } else {
            $isProxy = false;
        }

        $request = new RequestService();
        try {
            $result = $request->get($itemParams['resource_url'], [], $header, $isProxy);
        } catch (RequestException $e) {
            $errorMessage = $e->getMessage();
        }

        $newResult = [];

        if (!empty($result)) {
            $res = json_decode($result['data'], ture);

            $short_content_selector = json_decode($itemParams['short_content_selector'], true);

            $filter = explode('.', $short_content_selector['filter']);

            $newResult = $res[$filter[0]];
        }
        if ($isTest == 1) {
            $item['error_message'] = $errorMessage;
        }

        $item['end_time'] = date('Y-m-d H:i:s');
        if (!empty($itemParams['short_content_selector'])) {
            $item['short_content_selector'] = $newResult;
        } else {
            $item['short_content_selector'] = '';
        }

        if (!empty($itemParams['long_content_selector'])) {
            $item['long_content_selector'] = $newResult;
        } else {
            $item['long_content_selector'] = '';
        }

        $this->__createCrawlResult($item);
    }

    /**
     * __createCrawlResult
     * 保存抓取结果
     *
     * @return array
     */
    private function __createCrawlResult($item)
    {
        $httpService = new HttpService();
        try {

            $httpService->post(config('url.jinse_open_url') . '/v1/item/result/dispatch', $item);

        } catch (\Exception $e) {
            Log::debug('[ItemCrawl] request dispatch api error_message: ' . $e->getMessage());
        }
        return true;
    }
}
