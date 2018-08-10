<?php

namespace App\Console\Commands;

use App\Models\V2\AlarmResult;
use App\Services\InternalAPIV2Service;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Console\Command;
use Log;

class ProxyAlarm extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'proxy:alarm {proxy : verified proxy}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitoring the availability of the agent';

    protected $targets = [
        'https://www.google.com',
        'https://www.facebook.com'
    ];

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
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle()
    {
        $alarm = 0;

        $requestParams = [
            'timeout'  => 10000,
        ];

        try {
            $client = new Client($requestParams);

            foreach ($this->targets as $target) {
                $response = $client->request('get', $target, ['proxy' => 'tcp://' . $this->argument('proxy'), 'stream' => true, 'connect_timeout' => 3]);

                if ($response->getStatusCode() != 200) {
                    $alarm++;
                }
            }

            if ($alarm == count($this->targets)) {
                $data = [
                    'type' => AlarmResult::TYPE_WEWORK,
                    'content' => '服务器代理失效，请修复！',
                    'wework' => config('alarm.alarm_recipient')
                ];

                $result = InternalAPIV2Service::post('/alarm_result', $data);
                if (!$result) {
                    Log::debug('[ProxyAlarm handle] create alarm_result is failed');
                    return false;
                }
            }
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                Log::debug('[ProxyAlarm handle] guzzle request error, error:' . $e->hasResponse());
            }
        }
        return true;
    }
}
