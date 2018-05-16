<?php

namespace App\Listeners;

use App\Models\Item;
use App\Services\APIService;
use Log;
use App\Events\DataResultReportEvent;
use Illuminate\Contracts\Queue\ShouldQueue;

class DataResultReportListener implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  DataResultReportEvent  $event
     * @return void
     */
    public function handle(DataResultReportEvent $event)
    {
        Log::debug('[DataResultReportListener handle] ------- start -------');
        $data = $event->data;

        //这批数据的来源同一个script
        $scriptId = $data[0]['script_id'];

        //通过script_id映射task_id
        $taskId = config('data.'.$scriptId);
        //查询抓取任务详情
        $task = Item::find($taskId);

        if (empty($task)) {
            Log::debug('[DataResultReportListener] Item is not found');
            return true;
        }
        //获取选择器内容
        $short_content_selector = $task->short_content_selector;
        $selector = json_decode($short_content_selector,true);

        //获取选择器中的元素
        $selectorKeys = array_keys($selector);

        //去除数组中的元素remove_images
        foreach ($selectorKeys as $key => $selectorKey){
            if ($selectorKey == 'remove_images') {
                unset($selectorKeys[$key]);
            }
        }

        $newData = [];
        $postNum = 0;

        foreach($data as $info){

            //遍历上报的字段
            foreach($selectorKeys as $selectorKey){

                //判断上报的字段是否存在
                if (!array_key_exists($selectorKey, $info)) {

                    //上报字段是否为images
                    if ($selectorKey == 'images') {
                        $newData[$postNum][$selectorKey] = [];
                    } else {
                        $newData[$postNum][$selectorKey] = "";
                    }
                    continue;
                }

                $newData[$postNum][$selectorKey] = $info[$selectorKey];
            }
            $newData[$postNum]['task_id'] = 612;
            $postNum += 1;
        }

        $params['is_test'] = 1;
        $params['result'] = json_encode($newData, JSON_UNESCAPED_UNICODE);
        APIService::internalPost('/internal/item/result/report', $params);
    }
}
