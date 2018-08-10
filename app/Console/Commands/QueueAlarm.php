<?php

namespace App\Console\Commands;

use App\Models\V2\AlarmResult;
use App\Services\InternalAPIV2Service;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Console\Command;
use Log;

class QueueAlarm extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:alarm {vhost} {queue}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitoring queue exception';

    /**
     * The queue url
     * @var string
     */
    protected $baseUri = 'http://192.168.0.39:15672/api/queues/';

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
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle()
    {
        $requestParams = [
            'base_uri' => $this->baseUri,
            'timeout'  => 3000,
        ];

        try {
            $client = new Client($requestParams);
            $response = $client->request('get', $this->argument('vhost') . '/' . $this->argument('queue'), ['auth' => ['admin', 'admin']]);
            Log::debug('[ProxyAlarm handle] guzzle request uri, uri:' . $this->baseUri . $this->argument('vhost') . '/' . $this->argument('queue'));

            $mqResult = json_decode((string)$response->getBody(), true);
            if ($mqResult['messages_ready'] && $mqResult['message_stats']['deliver_details']['rate'] == 0) {
                $data = [
                    'type' => AlarmResult::TYPE_WEWORK,
                    'content' => $this->argument('vhost') . '/' . $this->argument('queue') . '队列异常！',
                    'wework' => config('alarm.alarm_recipient')
                ];

                $result = InternalAPIV2Service::post('/alarm_result', $data);
                if (!$result) {
                    Log::debug('[QueueAlarm handle] create alarm_result is failed');
                    return false;
                }
            }
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                Log::debug('[ProxyAlarm handle] guzzle request error, error:' . $e->getResponse());
                return false;
            }
            Log::debug('[ProxyAlarm handle] guzzle request error, error:' . $e);
        }
        return true;
    }
}
