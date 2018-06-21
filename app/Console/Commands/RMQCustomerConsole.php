<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\InternalAPIService;
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

            $type = '';
            switch ($customer['exchange_type']) {
                case Project::EXCHANGE_TYPE_DIRECT :
                    $type = 'direct';
                    break;
                case Project::EXCHANGE_TYPE_FANOUT :
                    $type = 'fanout';
                    break;
                case Project::EXCHANGE_TYPE_ROUTE :
                    $type = 'route';
                    break;
                default:
                    break;
            }
            if (empty($type) || empty($customer['vhost']) || empty($customer['exchange']) || empty($customer['queue'])) {
                Log::debug('[SaveDataListener error] customer id ' . $customer['id']);
                return false;
            }
            $option = [
                'server' => [
                    'vhost' => $customer['vhost'],
                ],
                'type' => $type,
                'exchange' => $customer['exchange'],
                'queue' => $customer['queue']
            ];
            $rmq = AMQP::getInstance($option);
            $rmq->consume($customer['queue'], $this->callback());
        } catch (Exception $e) {
            Log::debug('11');
            throw $e;
        }
    }

    private function getCustomer($type, $id)
    {
        switch ($type) {
                case 'project' :
                    $customer = Project::find($id)->toArray();
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

    private function callback()
    {
        $this->info(1);
        return function($msg) {
            $this->info($msg->body);
            // $this->info($customer_path);
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
    }
}
