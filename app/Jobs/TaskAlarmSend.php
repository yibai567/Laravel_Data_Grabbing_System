<?php

namespace App\Jobs;

use App\Services\WeWorkService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class TaskAlarmSend implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $send_message;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($send_message)
    {
        $this->send_message = $send_message;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (empty($this->send_message)) {
            return false;
        }

        $alarmInfo = json_decode($this->send_message, true);

        //企业微信号发送
        if (!empty($alarmInfo['receive_wework'])) {

            $receiveWework = explode(',', $alarmInfo['receive_wework']);

            foreach ($receiveWework as $value) {
                try {
                    $weWork = new WeWorkService();
                    $weWork->pushMessage('text', $alarmInfo['content'], $value);
                } catch (\Exception $e) {
                    infoLog('企业微信发送通知异常', $e);
                    return false;
                }
            }
        }
        //email发送
        //手机号发送
    }
}
