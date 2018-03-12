<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
$api = app('Dingo\Api\Routing\Router');

$api->version(
    'v1',
    ['namespace' => 'App\Http\Controllers\Api\V1', 'prefix'=>'v1'],
    function (Dingo\Api\Routing\Router $api) {
        $api->get('/test', function(){
            echo 'test';
        });
        $api->post('/crawl/task', 'CrawlTaskController@create');
        $api->post('/crawl/task/status', 'CrawlTaskController@updateStatus');
        $api->post('/crawl/task/stop', 'CrawlTaskController@stop');
        $api->post('/crawl/task/script', 'CrawlTaskController@createScript');
        $api->post('/crawl/task/preview', 'CrawlTaskController@preview');
        $api->post('/crawl/task/start','CrawlTaskController@start');
        $api->post('/crawl/result', 'CrawlResultController@create');
        $api->post('/crawl/result/batch_result', 'CrawlResultController@createByBatch');
    }
);
