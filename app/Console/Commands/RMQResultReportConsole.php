<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RabbitMQService;
use App\Services\InternalAPIService;
use App\Models\Script;

class RMQResultReportConsole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:resultReport';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '结果上报';

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
        $rabbitMQ = new RabbitMQService();
        $result = $rabbitMQ->get('result_report');
        if (empty($result)) {
            $this->info('result_report 队列暂无数据，请稍后重试');
            return false;
        }

        if (!isset($result['body']['task_id']) || !isset($result['body']['id'])) {
            $this->info('body 结构体格式错误 '. json_encode($result['body']));
            return false;
        }

        // 根据 task_id 获取 task 信息
        $task = InternalAPIService::get('/task', ['id' => $result['body']['task_id']]);
        if (empty($task)) {
            $this->info('task empty ,task_id = ' . $task['script_id']);
            return false;
        }

        // 根据 script_id 获取 script 信息
        $script = InternalAPIService::get('/script', ['id' => $task['script_id']]);
        if (empty($script)) {
            $this->info('script empty ,script_id = ' . $task['script_id']);
            return false;
        }

        // 不需要上报直接
        if ($script['is_report'] != Script::REPORT_DATA_TRUE) {
            $rabbitMQ->ack();
            return true;
        }

        // 需要下载图片
        if ($script['is_download'] == Script::DOWNLOAD_IMAGE_TRUE) {
            // 根据 dataId 获取 data 信息
            $data = InternalAPIService::get('/data', ['id' => $result['body']['id']]);
            if (empty($data)) {
                $this->info('data empty ,id = ' . $result['body']['id']);
                return false;
            }
            if ($data['img_remaining_step'] !== 0) {
                $rabbitMQ->nack();
                return true;
            }
        }

        // 上报接口
        $data = InternalAPIService::post('/v1/data/batch/report', ['ids' => $result['body']['id']]);
        if ($data) {
            $rabbitMQ->ack();
        } else {
            $rabbitMQ->nack();
        }
    }
}
