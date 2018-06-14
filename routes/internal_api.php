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
    ['namespace' => 'App\Http\Controllers\InternalAPIV2', 'prefix' => 'internal'],
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

        $api->post('/item_run_log','ItemRunLogController@create');
        $api->get('/item_run_logs','ItemRunLogController@all');

        $api->post('/item_run_log/status/success','ItemRunLogController@updateStatusSuccess');
        $api->post('/item_run_log/status/fail','ItemRunLogController@updateStatusFail');

        $api->get('/item_run_log/item','ItemRunLogController@getByItemId');
        $api->post('/item_run_log/update','ItemRunLogController@update');
        $api->get('/item_run_log','ItemRunLogController@retrieve');

        $api->post('/item','ItemController@create');
        $api->post('/item/update', 'ItemController@update');
        $api->post('/item/delete', 'ItemController@delete');
        $api->get('/item', 'ItemController@retrieve');
        $api->post('/item/start', 'ItemController@start');
        $api->post('/item/stop', 'ItemController@stop');
        $api->post('/item/test', 'ItemController@test');
        $api->post('/item/last_job/update', 'ItemController@updateLastJobAt');

        $api->post('/item/result/report', 'PlatformReportController@itemResultReport');

        $api->post('/item/status/test_success', 'ItemController@updateStatusTestSuccess');
        $api->post('/item/status/test_fail', 'ItemController@updateStatusTestFail');

        $api->get('/item/update/current_lengths','QueueInfoController@updateCurrentLength');

        $api->get('/item/results', 'ItemResultController@allByLast');
        $api->post('/item/result/html', 'ItemResultController@createHtml');
        $api->post('/item/result/image', 'ItemResultController@updateImage');
        $api->post('/item/result/capture', 'ItemResultController@updateCapture');
        $api->post('/item/result/status/fail', 'ItemResultController@updateStatusFail');

        $api->get('/item/test_result', 'ItemTestResultController@getTestResult');
        $api->post('/item/test_result', 'ItemTestResultController@create');
        $api->post('/item/test_result/html', 'ItemTestResultController@updateHtml');
        $api->post('/item/test_result/image', 'ItemTestResultController@updateImage');
        $api->post('/item/test_result/capture', 'ItemTestResultController@updateCapture');

        $api->post('/image/upload', 'ImageController@upload');

        $api->post('/image/get_by_result', 'ImageController@getByResult');
        $api->post('/image/download', 'ImageController@download');
        $api->post('/image/replace', 'ImageController@replacement');


        $api->post('/script','ScriptController@create');
        $api->post('/script/update','ScriptController@update');
        $api->get('/script','ScriptController@retrieve');
        $api->get('/scripts','ScriptController@all');
        $api->get('/script/publish','ScriptController@publishScript');
        $api->get('/script/queue','ScriptController@getByQueueName');

        $api->post('/script_model','ScriptModelController@create');
        $api->post('/script_model/update','ScriptModelController@update');
        $api->get('/script_model','ScriptModelController@retrieve');
        $api->get('/script_models','ScriptModelController@all');
        $api->get('/script_models/ids','ScriptModelController@listByIds');
        $api->get('/script_models/languages_type/{languages_type}','ScriptModelController@listByLanguagesType');

        $api->post('/data/results','DataController@batchCreate');
        $api->post('/datas','DataController@batchSave');
        $api->get('/data','DataController@retrieve');
        $api->get('/datas/ids','DataController@listByIds');
        $api->get('/datas/task_run_log_id','DataController@listByTaskRunLogId');
        $api->post('/datas/update/task_run_log_id','DataController@updateByTaskRunLogId');

        $api->post('/task_statistics/update','TaskStatisticsController@update');

        $api->post('/task_run_log','TaskRunLogController@create');
        $api->post('/task_run_log/status/success','TaskRunLogController@updateStatusSuccess');
        $api->post('/task_run_log/status/fail','TaskRunLogController@updateStatusFail');
        $api->get('/task_run_log','TaskRunLogController@retrieve');

        $api->get('/task','TaskController@retrieve');
        //收集资源
        $api->post('/quirement', 'QuirementPoolController@create');
        $api->post('/quirement/update', 'QuirementPoolController@update');
        $api->get('/quirements', 'QuirementPoolController@all');
        $api->get('/quirement', 'QuirementPoolController@retrieve');
        $api->get('/quirement/update_status','QuirementPoolController@updateStatus');

        //区块链新闻
        $api->get('/block_news','BlockNewsController@all');
        $api->get('/block_news/companies', 'QuirementPoolController@getCompanies');
        $api->get('/block_news/total','BlockNewsController@getTotal');



    }
);
