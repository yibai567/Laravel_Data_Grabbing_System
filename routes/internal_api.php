<?php

$api = app('Dingo\Api\Routing\Router');

$api->version(
    'v1',
    ['namespace' => 'App\Http\Controllers\InternalAPI\Basic', 'prefix' => 'internal/basic'],
    function (Dingo\Api\Routing\Router $api) {
        $api->get('/test', function(){
            echo 'test';
        });
        $api->post('/crawl/results', 'CrawlResultController@createForBatch');
        $api->get('/crawl/results', 'CrawlResultController@all');
        $api->post('/crawl/result/search', 'CrawlResultController@search');
        $api->post('/crawl/result/search_v2', 'CrawlResultController@searchV2');
        $api->get('/crawl/task', 'CrawlTaskController@retrieve');
        $api->post('/crawl/task', 'CrawlTaskController@create');
        $api->get('/crawl/task/search', 'CrawlTaskController@search');
        $api->post('/crawl/task/update','CrawlTaskController@update');
        $api->get('/crawl/tasks/ids','CrawlTaskController@listByIds');
    }
);


$api->version(
    'v1',
    ['namespace' => 'App\Http\Controllers\InternalAPI', 'prefix' => 'internal'],
    function (Dingo\Api\Routing\Router $api) {
        $api->get('/test', function(){
            echo 'test';
        });
        $api->post('/crawl/task', 'CrawlTaskController@create');
        $api->get('/crawl/task', 'CrawlTaskController@retrieve');
        $api->post('/crawl/task/stop', 'CrawlTaskController@stop');
        $api->get('/crawl/task/queue/name', 'CrawlTaskController@getByQueueName');
        $api->get('/crawl/task/queue/info', 'CrawlTaskController@getQueueInfo');
        $api->post('/crawl/task/test', 'CrawlTaskController@test');
        $api->post('/crawl/task/start', 'CrawlTaskController@start');
        $api->post('/crawl/results', 'CrawlResultController@createForBatch');
        $api->post('/crawl/results/json', 'CrawlResultV2Controller@saveAllToJson');
        $api->post('/crawl/results/html', 'CrawlResultV2Controller@saveAllToHtml');
        $api->post('/crawl/result/test', 'CrawlResultV2Controller@saveToTest');
        $api->get('/crawl/tasks/ids','CrawlTaskController@listByIds');
        $api->post('/crawl/task/update','CrawlTaskController@update');

        // new route
        $api->get('/queue_info/update/current_lengths','QueueInfoController@updateCurrentLength');
        $api->get('/queue_info/job','QueueInfoController@getJob');
        $api->post('/queue_info/job','QueueInfoController@createJob');
    }
);
