<?php

namespace App\Console\Commands;

use App\Services\RequestService;
use App\Services\APIService;
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
        $queueInfo = APIService::openGet('/v1/queue_info/job', ['id' => $queueId]);
        if (empty($queueInfo)) {
            echo "没有可处理的任务 \n";
            return false;
        }

        $this->__request($queueInfo, $isTest, $isProxy);
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

        $request = new RequestService();
        try {
            $result = $request->get($itemParams['resource_url'], [], $header, $isProxy);
        } catch (RequestException $e) {
            $errorMessage = $e->getMessage();
        }

        $newResult = [];

        if (!empty($result)) {
            $res = json_decode($result['data'], ture);
            $newResult = $res['data'];
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
        dd($item);

        // $crawlResult['start_time'] = date('Y-m-d H:i:s');
        // $header = [];
        // if (!empty($params['header'])) {
        //     $header = json_decode($params['header'], ture);
        // }
        // $request = new RequestService();
        // $result = $request->get($params['url'], [], $header, $params['isProxy']);
        // if (empty($result)) {
        //     echo sprintf("__request start params task_id = %d result not found \n", $params['task_id']);
        //     return false;
        // }
        // $crawlResult['task_id'] = $params['task_id'];
        // $crawlResult['is_test'] = $isTest;
        // $crawlResult['end_time'] = date('Y-m-d H:i:s');

        // $newResult = json_decode($result['data'], ture);
        // $crawlResult['result'] = $newResult['data'];
        // echo sprintf("__request end params task_id = %d \n", $params['task_id']);

        // $this->__createCrawlResult($crawlResult);
    }

    /**
     * __createCrawlResult
     * 保存抓取结果
     *
     * @return array
     */
    private function __createCrawlResult($crawlResult)
    {
        $data = APIService::openPost('/v1/crawl/result/dispatch', $crawlResult, 'json');
        echo sprintf("__createCrawlResult end res = %s \n", json_encode($data));

        return true;
    }
}
