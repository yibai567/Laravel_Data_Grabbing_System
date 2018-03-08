<?php

namespace App\Logging;

class CustomizeFormatter
{
    /**
     * Customize the given logger instance.
     *
     * @param  \Illuminate\Log\Logger  $logger
     * @return void
     */
    public function __invoke($logger)
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter(new \Monolog\Formatter\LogstashFormatter(config('logging.application_name'), null, '', $contextPrefix = '', 1));

            $handler->pushProcessor(
                new \Monolog\Processor\WebProcessor(null, array(
                        'url' => 'REQUEST_URI',
                        'ip' => 'REMOTE_ADDR',
                        'real_ip' => 'HTTP_X_FORWARDED_FOR',
                        'http_method' => 'REQUEST_METHOD',
                        'server' => 'SERVER_NAME',
                        'referrer' => 'HTTP_REFERER',
                    )
                )
            );
        }
    }
}
