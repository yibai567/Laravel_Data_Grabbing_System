<?php

namespace App\Console\Commands;

use App\Services\RequestService;
use App\Services\APIService;
use App\Models\CrawlTask;
use App\Models\CrawlResult;
use App\Services\CrawlTaskService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class TaskCrawl extends Command
{
    //private $_testQueueName = 'crawl_task_http_test';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:crawl {queue_name} {is_test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'task crawl process';

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
        $queueName = $this->argument('queue_name');
        $isTest = (int)$this->argument('is_test');
        if (empty($queueName)) {
            echo "参数 queue_name 不能为空 \n";
        }

        if ($isTest != CrawlResult::IS_TEST_TRUE && $isTest != CrawlResult::IS_TEST_FALSE) {
            echo "参数 is_test 不能为空 值是1或2 \n";
        }

        $taskList = $this->__getByQueueName($queueName);

        if (empty($taskList)) {
            echo "没有可处理的任务 \n";
        }

        foreach ($taskList as $value) {
            try {
                    $this->__request($value, $isTest);
                } catch (\Exception $e) {
                    continue;
                }
            }
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
    private function __request($params, $isTest)
    {
        echo sprintf("__request start params task_id = %d, url = %s \n", $params['task_id'], $params['url']);
        $crawlResult = [];
        $newResult = [];
        $crawlResult['start_time'] = date('Y-m-d H:i:s');
        $header = [];
        if (!empty($params['header'])) {
            $header = json_decode($params['header'], ture);
        }
        $request = new RequestService();
        $result = $request->get($params['url'], [], $header, $params['isProxy']);
        if (empty($result)) {
            echo sprintf("__request start params task_id = %d result not found \n", $params['task_id']);
            return false;
        }
        $crawlResult['task_id'] = $params['task_id'];
        $crawlResult['is_test'] = $isTest;
        $crawlResult['end_time'] = date('Y-m-d H:i:s');

        $newResult = json_decode($result['data'], ture);
        $crawlResult['result'] = $newResult['data'];
        echo sprintf("__request end params task_id = %d \n", $params['task_id']);

        $this->__createCrawlResult($crawlResult);
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
