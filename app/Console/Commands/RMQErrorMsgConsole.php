<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RabbitMQService;
use App\Services\InternalAPIService;
use App\Models\ErrorMessage;

class RMQErrorMsgConsole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:error_msg';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '错误收集';

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
            $rabbitMQ->consume('error_msg', $this->callback());
        } catch (Exception $e) {
            \Log::debug('11');
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
                $rabbitMQ->errorMsg($msg->body, 'error_msg 队列暂无数据，请稍后重试');
                return false;
            }

            if (!isset($result['body']['raw']) || !isset($result['body']['msg'])) {
                $rabbitMQ->errorMsg($msg->body, 'body 结构体格式错误');
                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                return false;
            }

            ErrorMessage::create($result['body']);
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
    }
}
