<?php

$api = app('Dingo\Api\Routing\Router');

$api->version(
    'v1',
    ['namespace' => 'App\Http\Controllers\InternalAPI\Basic', 'prefix' => 'internal/basic'],
    function (Dingo\Api\Routing\Router $api) {
        $api->get('/test', function(){
            echo 'test';
        });
        //$api->post('/crawl/result', 'CrawlResultController@create');
        $api->post('/crawl/result/batch_result', 'CrawlResultController@createByBatch');

        $api->get('/crawl/task', 'CrawlTaskController@retrieve');
        $api->post('/crawl/task', 'CrawlTaskController@create');
        $api->post('/crawl/task/status','CrawlTaskController@updateStatus');
        $api->post('/crawl/task/result', 'CrawlTaskController@updateResult');
        $api->get('/crawl/tasks', 'CrawlTaskController@all');

        $api->post('/crawl/task/script', 'CrawlTaskController@updateScriptFile');
        $api->post('/crawl/task/last_job_at', 'CrawlTaskController@updateLastJobAt');
        $api->get('/crawl/result/is_task_exist', 'CrawlResultController@isTaskExist');
        $api->get('/crawl/node/usable', 'CrawlNodeController@getUsableNode');

        $api->post('/crawl/node_task/start', 'CrawlNodeTaskController@start');
        $api->post('/crawl/node_task', 'CrawlNodeTaskController@create');
        $api->post('/crawl/node_task/stop', 'CrawlNodeTaskController@stop');
        $api->get('/crawl/node_task/started', 'CrawlNodeTaskController@getStartedTaskByTaskId');
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
        $api->post('/crawl/task/status','CrawlTaskController@updateStatus');
        $api->post('/crawl/task/stop', 'CrawlTaskController@stop');

        $api->post('/crawl/task/script', 'CrawlTaskController@createScript');
        $api->post('/crawl/task/preview', 'CrawlTaskController@preview');
        $api->post('/crawl/task/start', 'CrawlTaskController@start');
        $api->post('/crawl/task/result', 'CrawlTaskController@updateResult');
        $api->get('/crawl/tasks', 'CrawlTaskController@all');
        $api->post('/crawl/task/last_job_at', 'CrawlTaskController@updateLastJobAt');
        //$api->post('/crawl/result', 'CrawlResultController@create');
        $api->post('/crawl/result/batch_result', 'CrawlResultController@createByBatch');

        $api->post('/crawl/node_task', 'CrawlNodeTaskController@create');
        $api->post('/crawl/node_task/stop', 'CrawlNodeTaskController@stop');
        $api->post('/crawl/node_task/start', 'CrawlNodeTaskController@start');
    }
);
