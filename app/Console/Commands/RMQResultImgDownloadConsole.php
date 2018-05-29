<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RabbitMQService;
use App\Services\InternalAPIService;

class RMQResultImgDownloadConsole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:resultImgDownload';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '图片下载';

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
            $rabbitMQ->consume('img_download', $this->callback());
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

            if (!isset($result['body']['image_url'])) {
                $rabbitMQ->errorMsg($msg->body, 'body 结构体格式错误');
                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                return false;
            }

            $params = [
                "image_url" => $result['body']['image_url'],
                "is_proxy" => $result['header']['is_proxy']
            ];
            //请求图片下载接口，返回原是图片url和阿里云图片url
            try {
                $res = InternalAPIService::post('/image/download', $params);
            } catch (\Dingo\Api\Exception\ResourceException $e) {
                $rabbitMQ->errorMsg($msg->body, 'img uploade fail' . $params['image_url']);
                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                return false;
            }

            if (empty($res['img_id'])) {
                $rabbitMQ->errorMsg($msg->body, 'img_id empty image_url = ' . $params['image_url']);
                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                return false;
            }

            $message = [
                "original_img_url" => $res['image_url'],
                "img_id" => $res['img_id']
            ];

            $headers = [
                "vhost" => "crawl",
                "exchange" => "image",
                "routing_key" => "replace",
                "data_id" => $result['header']['data_id']
            ];
            //调用队列
            $rabbitMQ->create('image', 'replace', $message, $headers);
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
    }
}