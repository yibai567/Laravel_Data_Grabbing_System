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
        $api->get('/crawl/result/search', 'CrawlResultController@search');
        $api->get('/crawl/task', 'CrawlTaskController@retrieve');
        $api->post('/crawl/task', 'CrawlTaskController@create');
        $api->post('/crawl/task/update','CrawlTaskController@update');
        $api->get('/crawl/tasks', 'CrawlTaskController@all');
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
        $api->post('/crawl/task/stop', 'CrawlTaskController@stop');
        $api->get('/crawl/task/queue/name', 'CrawlTaskController@getByQueueName');
        $api->get('/crawl/task/queue/info', 'CrawlTaskController@getQueueInfo');
        $api->post('/crawl/task/test', 'CrawlTaskController@test');
        $api->post('/crawl/task/start', 'CrawlTaskController@start');
        $api->get('/crawl/tasks', 'CrawlTaskController@all');
        $api->post('/crawl/results', 'CrawlResultController@createForBatch');

    }
);
