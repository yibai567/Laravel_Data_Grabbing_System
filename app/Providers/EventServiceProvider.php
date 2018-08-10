<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\Event' => [
            'App\Listeners\EventListener',
        ],
        'App\Events\TaskPreview' => [
            // 任务测试
            'App\Listeners\TaskPreviewListener',
        ],

        'App\Events\SaveDataEvent' => [
            'App\Listeners\SaveDataListener',
        ],

        'App\Events\ProjectResultEvent' => [
            'App\Listeners\ProjectResultListener',
        ],

        'App\Events\ConverterTaskEvent' => [
            'App\Listeners\ConverterTaskListener',
        ],

        'App\Events\SaveAlarmResult' => [
            'App\Listeners\SaveAlarmResultListener',
        ],

        'App\Events\StatisticsEvent' => [
            'App\Listeners\StatisticsListener',
        ],
        // 微信服务事件
        'App\Events\WechatServerEvent' => [
            'App\Listeners\WechatServerListener',
        ],

        // SqlListener监听QueryExecuted
        'Illuminate\Database\Events\QueryExecuted' => [
            'App\Listeners\SqlListener',
        ],

    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
