<?php

namespace App\Console\Commands;

use App\Services\HtmlEntitieService;
use Illuminate\Console\Command;
use App\Models\HistoryTopic;

class UpdateHtmlConsole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl:update:html';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '修复数据库';

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
        $i = 0;
        $this->info('crawl:update:html start！');
        while ($i < 10000) {
            // $this->info("当前" . $i);
            $this->__updateQueueData();
            $i++;
        }
    }

    /**
     * __updateQueueData
     * 更新队列逻辑
     */
    private function __updateQueueData()
    {

        $historyTopic = HistoryTopic::select(['id', 'content'])
                        ->where('status', HistoryTopic::STATUS_FINISH)
                        ->where('id' ,'<' ,53461)
                        ->orderBy('id', 'asc')
                        ->first();
        if (empty($historyTopic)) {
            $this->info('暂无数据');
            return false;
        }
        $this->info($historyTopic->id);
        $historyTopic->content = HtmlEntitieService::decode($historyTopic->content);
        $historyTopic->status = HistoryTopic::STATUS_NEW;
        $historyTopic->save();
    }
}
