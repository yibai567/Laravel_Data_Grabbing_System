<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RabbitMQService;
use App\Services\InternalAPIService;

class RMQResultImgReplaceConsole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:result_img_replace';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '图片替换';

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
            $rabbitMQ->consume('img_replace', $this->callback());
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

            if (empty($result['body']['data_id'])) {
                $rabbitMQ->errorMsg($msg->body, 'data_id 不能为空');
                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                return false;
            }
            if (empty($result['body']['original_img_url'])) {
                $rabbitMQ->errorMsg($msg->body, 'original_img_url 不能为空');
                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                return false;
            }

            if (empty($result['body']['img_id'])) {
                $rabbitMQ->errorMsg($msg->body, 'img_id 不能为空');
                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                return false;
            }


            $params = [
                "data_id" => $result['body']['data_id'],
                "original_img_url" => $result['body']['original_img_url'],
                "img_id" => $result['body']['img_id']
            ];

            //请求图片替换接口，返回true
            $res = InternalAPIService::post('/image/replace', $params);

            if (empty($res)) {
                $rabbitMQ->errorMsg($msg->body, 'image replace return empty params = ' . $params);
                return false;
            }

            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
    }

}