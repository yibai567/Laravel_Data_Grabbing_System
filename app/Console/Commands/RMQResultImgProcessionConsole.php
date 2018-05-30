<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RabbitMQService;
use App\Services\InternalAPIService;
use App\Models\Script;

class RMQResultImgProcessionConsole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:result_img_processing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '结果是否需要处理图片脚本';

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
            $rabbitMQ->consume('result_img_processing', $this->callback());
        } catch (Exception $e) {
            \Log::debug('[rabbitmq:result_img_processing] error Exception');
            throw $e;
        }
    }
    public function callback()
    {
        return function($msg) {
            $this->info($msg->body);
            $rabbitMQ = new RabbitMQService();
            $result = json_decode($msg->body, true);

            if (!isset($result['body']['task_id']) || !isset($result['body']['id']) || !array_key_exists('thumbnail', $result['body']) || !array_key_exists('content', $result['body'])) {

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
            // 不需要下载图片直接从队列移除并退出
            if ($script['is_download'] != Script::DOWNLOAD_IMAGE_TRUE) {
                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                return true;
            }

            $result['header'] = [
                'exchange' => 'image',
                'routing_key' => 'download',
                'data_id' => $result['body']['id'],
                'task_id' => $result['body']['task_id'],
                'script_id' => $task['script_id'],
                'task_run_log_id' => $result['body']['task_run_log_id']
            ];

            $message = [
                'data_id' => $result['body']['id'],
                'thumbnail' => $result['body']['thumbnail'],
                'content' => $result['body']['content']
            ];

            $rabbitMQ->create('image', 'get_url', $message, $result['header']);
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
    }
}
