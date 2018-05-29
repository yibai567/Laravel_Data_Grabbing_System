<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RabbitMQService;
use App\Services\InternalAPIService;

class RMQResultImgExtractConsole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:resultImgExtract';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '图片提取';

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
            $rabbitMQ->consume('img_get_url', $this->callback());
        } catch (Exception $e) {
            \Log::debug('11');
            throw $e;
        }
    }
    public function callback()
    {
        return function($msg) {
            $this->info($msg->body);
            if (!empty($msg->body)) {
                $result = json_decode($msg->body, true);
            }
            $rabbitMQ = new RabbitMQService();

            //验证缩略图和富文本都为空 返回并删除队列
            if (empty($result['body']['thumbnail']) && empty($result['body']['content'])) {
                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                return false;
            }

            //请求图片提取接口 返回 urls
            $res = InternalAPIService::post('/image/get_by_result', $result['body']);
            if (empty($res) || empty($res['img_urls'])) {
                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                return false;
            }

            $headers = [
                "vhost" => "crawl",
                "exchange" => "image",
                "routing_key" => "download",
                "data_id" => $result['body']['data_id'],
                "is_proxy" => $res['is_proxy']
            ];
            foreach ($res['img_urls'] as $key => $value) {
                //调用队列
                $rabbitMQ = new RabbitMQService();
                $rabbitMQ->create('image', 'download', ['image_url' => $value], $headers);
            }
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
    }
}