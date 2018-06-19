<?php

namespace App\Console\Commands;

use App\Services\HttpService;
use App\Services\InternalAPIService;
use App\Services\RabbitMQService;
use Illuminate\Console\Command;
use Log;

class RMQProjectConsole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:project {project_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '分发项目';

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
            //根据传递project的id查询project
            $projectId = (int)$this->argument('project_id');
            $project = InternalAPIService::get('/project', ['id' => $projectId]);
            if (empty($project)) {
                return false;
            }
            //获取队列名称
            $queue = $project['queue'];

            $rabbitMQ = new RabbitMQService();
            $rabbitMQ->consume($queue, $this->callback($project));

        } catch (Exception $e) {
            Log::debug('[rabbitmq:project] error Exception');
            throw $e;
        }
    }

    public function callback($project)
    {
        return function($msg,$project) {
            $this->info($msg->body);
            $rabbitMQ = new RabbitMQService();
            $result = json_decode($msg->body, true);

            if (empty($result)) {
                $rabbitMQ->errorMsg($msg->body, 'project 队列暂无数据，请稍后重试');
                return false;
            }

            if (!isset($result['body']['task_id']) || !isset($result['body']['id'])) {
                $rabbitMQ->errorMsg($msg->body, 'body 结构体格式错误');
                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                return false;
            }

            //获取project的customer_path
            $customer_path = $project['customer_path'];

            // 保存接口
            $httpService = new HttpService();
            $data = $httpService->post($customer_path, $result['body']);

            if ($data) {
                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
            } else {
                $rabbitMQ->errorMsg($msg->body, 'post /v1/project error ');
                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
            }
            return true;
        };
    }

}
