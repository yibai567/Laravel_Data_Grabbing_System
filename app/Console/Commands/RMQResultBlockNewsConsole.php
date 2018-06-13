<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RabbitMQService;
use App\Services\InternalAPIService;
use App\Models\BlockNews;

class RMQResultBlockNewsConsole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:result_block_news';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '新闻入库';

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
            $rabbitMQ->consume('dispatch_news', $this->callback());
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

            if (empty($result['body'])) {
                return false;
            }

            if (empty($result['body']['id'])) {
                return false;
            }

            if (empty($result['body']['list_url'])) {
                return false;
            }

            if (empty($result['body']['result'])) {
                return false;
            }

            foreach ($result['body']['result'] as $key => $value) {
                $newData[] = [
                    "requirement_id" => $result['body']['id'],
                    "list_url" => $result['body']['list_url'],
                    "title" => $value['title'],
                    "content" => $value['content'],
                    "detail_url" => $value['detail_url'],
                    "show_time" => $value['show_time'],
                    "read_count" => $value['read_count'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
            }
            try {
                $result = BlockNews::insert($newData);
            } catch (\Dingo\Api\Exception\ResourceException $e) {
                $rabbitMQ->errorMsg($msg->body, '数据插入失败' . $newData);
                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                return false;
            }
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
            return true;
        };
    }
}
