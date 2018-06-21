<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\InternalAPIV2Service;
use App\Models\V2\Project;
use App\AMQP;
use Log;


class RMQCustomerConsole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:customer {type} {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '消费者';

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
            $type = $this->argument('type');
            if (!in_array($type, ['project', 'filter', 'action'])) {
                $this->error('{type} must is project or filter or action');
                return false;
            }
            $id = $this->argument('id');
            $customer = $this->getCustomer($type, $id);

            $exchangeType = '';
            switch ($customer['exchange_type']) {
                case Project::EXCHANGE_TYPE_DIRECT :
                    $exchangeType = 'direct';
                    break;
                case Project::EXCHANGE_TYPE_FANOUT :
                    $exchangeType = 'fanout';
                    break;
                case Project::EXCHANGE_TYPE_ROUTE :
                    $exchangeType = 'route';
                    break;
                default:
                    break;
            }
            if (empty($exchangeType) || empty($customer['vhost']) || empty($customer['exchange']) || empty($customer['queue'])) {
                Log::debug('[SaveDataListener error] customer id ' . $customer['id']);
                return false;
            }
            $option = [
                'server' => [
                    'vhost' => $customer['vhost'],
                ],
                'type' => $exchangeType,
                'exchange' => $customer['exchange'],
                'queue' => $customer['queue']
            ];
            $rmq = AMQP::getInstance($option);
            $rmq->prepareExchange();
            $rmq->prepareQueue();
            $rmq->queueBind();
            $rmq->consume($customer['queue'], $this->callback($customer['customer_path'], $type, $id));
        } catch (Exception $e) {
            Log::debug('11');
            throw $e;
        }
    }

    private function getCustomer($type, $id)
    {
        switch ($type) {
                case 'project' :
                    $customer = Project::find($id);
                    if (!empty($customer)) {
                        $customer->toArray();
                    }
                    break;
                case 'filter' :
                    // $type = 'fanout';
                    break;
                case 'action' :
                    // $type = 'route';
                    break;
                default:
                    break;
            }
        return $customer;
    }

    private function callback($customerPath, $type, $id)
    {
        return function($msg) use ($customerPath, $type, $id) {
            $message = json_decode($msg->body, true);
            switch ($type) {
                case 'project' :
                    $params = [
                        'data_id' => $message['id'],
                        'project_id' => $id,
                    ];
                    $result = InternalAPIV2Service::post($customerPath, $params);
                    if ($result) {
                        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                    }
                    break;
                case 'filter' :

                    break;
                case 'action' :

                    break;
                default:
                    break;
            }

        };
    }
}
