<?php

$api = app('Dingo\Api\Routing\Router');

$api->version(
    'v1',
    function (Dingo\Api\Routing\Router $api) {
        $api->group(
            ['namespace' => 'App\Http\Controllers\Api\V1', 'prefix' => 'v1'],
            function (Dingo\Api\Routing\Router $api) {
                $api->post('/crawl/task/generate_script','CrawlTaskController@generateScript');
                $api->post('/crawl/task', 'CrawlTaskController@create');
                $api->post('/crawl/task/execute','CrawlTaskController@execute');
                $api->post('/crawl/task/status','CrawlTaskController@updateStatus');
                $api->post('/crawl/result', 'CrawlResultController@create');
                $api->post('/crawl/result/list', 'CrawlResultController@pushList');
                $api->post('/crawl/task/startup','CrawlTaskController@startup');
                $api->post('/crawl/task/stop','CrawlTaskController@stop');
            }
        );
    }
);
