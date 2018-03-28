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
    protected $signature = 'task:crawl {is_test=2}';

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
        $is_test = (int)$this->argument('is_test');
        if ($is_test != CrawlResult::IS_TEST_TRUE && $is_test != CrawlResult::IS_TEST_FALSE) {
            echo "参数 is_test 必须是1或2";
        }

        if ($is_test == CrawlResult::IS_TEST_TRUE) {
            $queueName = 'crawl_task_json_test';
        } else {
            $queueName = 'crawl_task_noproxy_nowall_noajax_nologin_keep_json';
        }
        $taskList = $this->__getByQueueName($queueName);
        if (empty($taskList)) {
            echo "没有可处理的任务";
        }

        foreach ($taskList as $value) {
            try {
                    $this->__request($value, $is_test);
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
        $result = APIService::internalGet('/internal/crawl/task/queue/name?name=' . $name);
        return $result;
    }
    /**
     * __request
     * 抓取请求
     *
     * @return array
     */
    private function __request($params, $is_test)
    {
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
            return false;
        }
        $crawlResult['task_id'] = $params['task_id'];
        $crawlResult['is_test'] = $is_test;
        $crawlResult['end_time'] = date('Y-m-d H:i:s');

        $newResult = json_decode($result['data'], ture);
        $crawlResult['result'] = $newResult['data'];
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
        return $data;
    }
}
