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

    protected $alarm_parmas;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($alarm_parmas)
    {
        $this->alarm_parmas = $alarm_parmas;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (empty($this->alarm_parmas)) {
            return false;
        }

        $alarmInfo = json_decode($this->alarm_parmas, true);
        //企业微信号发送
        if (!empty($alarmInfo['receive_wework'])) {

            $receiveWework = explode(',', $alarmInfo['receive_wework']);

            foreach ($receiveWework as $value) {
                try {
                    $weWork = new WeWorkService();
                    $weWork->pushMessage('text', "尊敬的管理员：" . $value . "\n" . $alarmInfo['content'], $value);
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
