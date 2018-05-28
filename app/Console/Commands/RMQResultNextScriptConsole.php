<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RabbitMQService;
use App\Services\InternalAPIService;
use App\Models\Script;

class RMQResultNextScriptConsole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:resultNextScript';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '下一步脚本';

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
        $result = $rabbitMQ->get('result_next_script');
        if (empty($result)) {
            $this->info('result_next_script 队列暂无数据，请稍后重试');
            return false;
        }

        if (!isset($result['body']['task_id']) || !isset($result['body']['id']) || !isset($result['body']['detail_url'])) {
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

        // 没有下一步脚本
        if (empty($script['next_script_id'])) {
            $rabbitMQ->ack();
            return true;
        }

        $newScript = InternalAPIService::get('/script', ['id' => $script['next_script_id']]);
        if (empty($newScript)) {
            $this->info('next_script empty ,script_id = ' . $script['next_script_id']);
            return false;
        }

        $result['header'] = [
            'data_id' => $result['body']['id'],
            'task_id' => $result['body']['task_id'],
            'script_id' => $script['next_script_id']
        ];

        switch ($newScript['languages_type']) {
            case Script::LANGUAGES_TYPE_CASPERJS:
                $routingKey = 'casperjs';
                break;

            case Script::LANGUAGES_TYPE_HTML:
                $routingKey = 'node';
                break;

            case Script::LANGUAGES_TYPE_API:
                $routingKey = 'node';
                break;

            default:
                $routingKey = '';
                break;
        }

        $taskRunLog = InternalAPIService::post('/task_run_log', ['task_id' => $result['body']['task_id'], 'start_job_at' => date('Y-m-d H:i:s')]);
        if (empty($taskRunLog)) {
            $this->info("task_run_log添加失败，请后重试");
            $rabbitMQ->nack();
        }
        $message = [
            'path' => 'script_' . $script['next_script_id'],
            'task_run_log_id' => $taskRunLog['id'],
            'url' => $result['body']['detail_url']
        ];

        $rabbitMQ->create('instant_task', $routingKey, $message, $result['header']);
        $rabbitMQ->ack();
    }
}
