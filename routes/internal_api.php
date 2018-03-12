<?php

$api = app('Dingo\Api\Routing\Router');

$api->version(
    'v1',
    ['namespace' => 'App\Http\Controllers\InternalAPI\Basic', 'prefix' => 'internal/basic'],
    function (Dingo\Api\Routing\Router $api) {
        $api->get('/crawl/task', 'CrawlTaskController@retrieve');
        $api->post('/crawl/task', 'CrawlTaskController@create');
        $api->post('/crawl/task/status','CrawlTaskController@updateStatus');
        $api->post('/crawl/result', 'CrawlResultController@create');
        $api->post('/crawl/result/push_list', 'CrawlResultController@pushList');
        $api->post('/crawl/task/update_script_last_generate_time', 'CrawlTaskController@updateScriptLastGenerateTime');
        $api->post('/crawl/task/update_script_file', 'CrawlTaskController@updateScriptFile');

        $api->get('/crawl/node/get_usable_node', 'CrawlNodeController@getUsableNode');
        $api->post('/crawl/node_task', 'CrawlNodeTaskController@create');
        $api->post('/crawl/node_task/start', 'CrawlNodeTaskController@startTask');
        $api->post('/crawl/node_task/stop', 'CrawlNodeTaskController@stopTask');
        $api->post('/crawl/node_task/get_startuped_task_by_task_id', 'CrawlNodeTaskController@getStartupedTaskByTaskId');

        // 新增
        $api->post('/crawl/batch_result', 'CrawlResultController@createByBatch');
        $api->post('/crawl/task/script_file', 'CrawlTaskController@updateScriptFile');

        $api->get('/crawl/node/usable', 'CrawlNodeController@getUsableNode');
        $api->post('/crawl/node_task/start', 'CrawlNodeTaskController@start');
        $api->post('/crawl/node_task/stop', 'CrawlNodeTaskController@stop');
        $api->get('/crawl/node_task/started', 'CrawlNodeTaskController@getStartedTaskByTaskId');
    }
);


$api->version(
    'v1',
    ['namespace' => 'App\Http\Controllers\InternalAPI', 'prefix' => 'internal'],
    function (Dingo\Api\Routing\Router $api) {
        $api->post('/crawl/task', 'CrawlTaskController@create');
        $api->post('/crawl/task/status','CrawlTaskController@updateStatus');
        $api->post('/crawl/result', 'CrawlResultController@create');
        $api->post('/crawl/result/push_list', 'CrawlResultController@pushList');
        $api->post('/crawl/task/generate_script', 'CrawlTaskController@generateScript');
        $api->post('/crawl/task/execute', 'CrawlTaskController@execute');
        $api->post('/crawl/task/startup', 'CrawlTaskController@startup');
        $api->post('/crawl/task/stop', 'CrawlTaskController@stop');

        $api->post('/crawl/node_task', 'CrawlNodeTaskController@create');
        $api->post('/crawl/node_task/stop', 'CrawlNodeTaskController@stopTask');
        $api->post('/crawl/node_task/start', 'CrawlNodeTaskController@startTask');

        // 新增
        $api->post('/crawl/batch_result', 'CrawlResultController@createByBatch');
        $api->post('/crawl/task/script', 'CrawlTaskController@createScript');
        $api->post('/crawl/task/preview', 'CrawlTaskController@preview');
        $api->post('/crawl/task/start', 'CrawlTaskController@start');
        $api->post('/crawl/node_task/stop', 'CrawlNodeTaskController@stop');
        $api->post('/crawl/node_task/start', 'CrawlNodeTaskController@start');
    }
);
