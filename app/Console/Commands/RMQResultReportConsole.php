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
    protected $signature = 'rabbitmq:result_report';

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
        try {
            $rabbitMQ = new RabbitMQService();
            $rabbitMQ->consume('result_report', $this->callback());
        } catch (Exception $e) {
            \Log::debug('[rabbitmq:result_report] error Exception');
            throw $e;
        }
    }

    public function callback()
    {
        return function($msg) {
            $this->info($msg->body);
            $rabbitMQ = new RabbitMQService();
            $result = json_decode($msg->body, true);

            if (empty($result)) {
                $rabbitMQ->errorMsg($msg->body, 'result_report 队列暂无数据，请稍后重试');
                return false;
            }

            if (!isset($result['body']['task_id']) || !isset($result['body']['id'])) {
                $rabbitMQ->errorMsg($msg->body, 'body 结构体格式错误');
                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                return false;
            }

            // 根据 task_id 获取 task 信息
            $task = InternalAPIService::get('/task', ['id' => $result['body']['task_id']]);
            if (empty($task)) {
                $rabbitMQ->errorMsg($msg->body, 'task empty ,task_id = ' . $task['script_id']);
                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                return false;
            }

            // 根据 script_id 获取 script 信息
            $script = InternalAPIService::get('/script', ['id' => $task['script_id']]);
            if (empty($script)) {
                $rabbitMQ->errorMsg($msg->body, 'script empty ,script_id = ' . $task['script_id']);
                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                return false;
            }

            // 不需要上报直接
            if ($script['is_report'] != Script::REPORT_DATA_TRUE) {
                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                return true;
            }

            // 需要下载图片
            if ($script['is_download'] == Script::DOWNLOAD_IMAGE_TRUE) {
                // 根据 dataId 获取 data 信息
                $data = InternalAPIService::get('/data', ['id' => $result['body']['id']]);
                if (empty($data)) {
                    $rabbitMQ->errorMsg($msg->body, 'data empty ,id = ' . $result['body']['id']);
                    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                    return false;
                }
                if ($data['img_remaining_step'] !== 0) {
                    $msg->delivery_info['channel']->basic_nack($msg->delivery_info['delivery_tag']);
                    return true;
                }
            }

            // 上报接口
            $data = InternalAPIService::post('/v1/data/batch/report', ['ids' => $result['body']['id']]);
            if ($data) {
                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
            } else {
                $msg->delivery_info['channel']->basic_nack($msg->delivery_info['delivery_tag']);
            }
        };
    }
}
