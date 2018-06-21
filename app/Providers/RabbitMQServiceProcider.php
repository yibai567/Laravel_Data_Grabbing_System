<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
// use PhpAmqpLib\Connection\AMQPStreamConnection;
// use PhpAmqpLib\Message\AMQPMessage;
// use PhpAmqpLib\Wire\AMQPTable;
use App\Services\AMQPService;

class RabbitMQServiceProcider extends ServiceProvider
{
    /**
     * 指定提供者加载是否延缓。
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('amqp', AMQPService::class);
    }

    // private function __client()
    // {
    //     $this->connection = new AMQPStreamConnection(config('rabbitmq.host'), config('rabbitmq.port'), config('rabbitmq.login'), config('rabbitmq.password'), config('rabbitmq.vhost'));
    //     $this->channel = $this->connection->channel();
    //     $this->channel->exchange_declare('error', 'fanout', false, true, false);
    //     $this->channel->queue_declare('error_msg', false, true, false, false);
    //     $this->channel->queue_bind('error_msg', 'error');
    // }
}

