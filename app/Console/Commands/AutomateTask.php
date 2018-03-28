<?php

namespace App\Console\Commands;

use App\Models\CrawlTask;
use App\Services\CrawlTaskService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class AutomateTask extends Command
{
    private $_proxys = [CrawlTask::IS_PROXY_TRUE => 'proxy', CrawlTask::IS_PROXY_FALSE => 'noproxy'];
    private $_cronTypes = [
        CrawlTask::CRON_TYPE_KEEP => 'keep',
        CrawlTask::CRON_TYPE_EVERY_MINUTE => 'every_minute',
        CrawlTask::CRON_TYPE_EVERY_FIVE_MINUTES => 'every_five_minutes',
        CrawlTask::CRON_TYPE_EVERY_TEN_MINUTES => 'every_ten_minutes',
        CrawlTask::CRON_TYPE_EVERY_FIFTEEN_MINUTES => 'every_fifteen_minutes',
        CrawlTask::CRON_TYPE_EVERY_TWENTY_MINUTES => 'every_twenty_minutes',
    ];
    private $_protocols = [CrawlTask::PROTOCOL_HTTP => 'http', CrawlTask::PROTOCOL_HTTPS => 'https'];
    private $_ajaxs = [CrawlTask::IS_AJAX_TRUE => 'ajax', CrawlTask::IS_AJAX_FALSE => 'noajax'];
    private $_logins = [CrawlTask::IS_LOGIN_TRUE => 'login', CrawlTask::IS_LOGIN_FALSE => 'nologin'];
    private $_walls = [CrawlTask::IS_WALL_TRUE => 'wall', CrawlTask::IS_WALL_FALSE => 'nowall'];
    private $_resource = [CrawlTask::RESOURCE_TYPE_HTML => 'html', CrawlTask::RESOURCE_TYPE_JSON => 'json'];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl:task:automate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'automate task process';

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
        $this->_processTask();
        return 0;
    }

    /**
     * 处理任务
     * @return int
     */
    private function _processTask()
    {
        // 获取任务
        $data = $this->_getData();
        if (count($data)) {
            //入库
            $this->_insert($data);
        }
        return 0;
    }

    /**
     * 获取所有任务列表
     * @return array
     */
    private function _getData()
    {
        $data = [];
        CrawlTask::where('status', CrawlTask::IS_START_UP)->chunk(500, function ($tasks) use(&$data) {
            if ($tasks) {
                $tasks = $tasks->toArray();
            } else {
                return false;
            }
            $result = $this->_formatData($tasks);
            $data = array_merge_recursive($data, $result);
            return true;
        });
        return $data;
    }

    /**
     * 整理数据
     * @param array $data
     */
    private function _formatData(array $data)
    {
        $result = [];
        foreach ($data as $item)
        {
            $key = $this->_getKey($item);
            $data = [
                'task_id' => $item['id'],
                'url' => $item['resource_url'],
            ];
            if ($item['resource_type'] == CrawlTask::RESOURCE_TYPE_JSON) {
                $data['header'] = $item['header'];
                $data['is_proxy'] = $item['is_proxy'];
            } else {
                $data['selector'] = $item['selectors'];
            }
            $result[$key][] = $data;
        }
        return $result;
    }

    /**
     * 数据入队
     * @param array $data
     */
    private function _insert(array $data)
    {
        foreach ($data as $key => $items) {
            if (Redis::connection('queue')->lLen($key) <= 0 && count($items) > 0 ) {
                foreach($items as $item) {
                    Redis::connection('queue')->lpush($key, json_encode($item));
                }
            } else {
                sleep(1);
            }
        }
        return true;
    }

    /**
     * 获取键名
     * @param $item
     * @return string
     */
    private function _getKey($item)
    {
        $protocol = $this->_protocols[$item['protocol']];
        $proxy = $this->_proxys[$item['is_proxy']];
        $wall = $this->_walls[$item['is_wall']];
        $ajax = $this->_ajaxs[$item['is_ajax']];
        $login = $this->_logins[$item['is_login']];
        $cronType = $this->_cronTypes[$item['cron_type']];
        $resourceType = $this->_resource[$item['resource_type']];
        switch ($item['resource_type']) {
            case CrawlTask::RESOURCE_TYPE_HTML:
                $key = 'crawl_task_' . $protocol . '_' . $proxy . '_' . $wall . '_' . $ajax . '_' . $login . '_' . $cronType . '_v2';
                break;
            case CrawlTask::RESOURCE_TYPE_JSON:
                $key = 'crawl_task_' . $proxy . '_' . $wall . '_' . $ajax . '_' . $login . '_' . $cronType . '_' . $resourceType;
                break;

            default:
                $key = 'crawl_task_' . $protocol . '_' . $proxy . '_' . $wall . '_' . $ajax . '_' . $login . '_' . $cronType;
                break;
        }
        return $key;
    }
}
