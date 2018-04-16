<?php
use Illuminate\Http\Request;
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
    ['namespace' => 'App\Http\Controllers\API\V1', 'prefix'=>'v1'],
    function (Dingo\Api\Routing\Router $api) {
        $api->get('/test', function(Illuminate\Http\Request $request){
            return 'test';
        });
        $api->post('/crawl/task', 'CrawlTaskController@create');
        $api->post('/crawl/task/update', 'CrawlTaskController@update');
        $api->post('/crawl/task/test', 'CrawlTaskController@test');
        $api->post('/crawl/task/stop', 'CrawlTaskController@stop');
        $api->post('/crawl/task/start','CrawlTaskController@start');
        $api->get('/crawl/tasks/ids','CrawlTaskController@listByIds');
        $api->get('/crawl/task/queue/name', 'CrawlTaskController@getByQueueName');
        $api->get('/crawl/task/queue/info', 'CrawlTaskController@getQueueInfo');
        $api->post('/crawl/results', 'CrawlResultController@createForBatch');
        $api->post('/crawl/result/dispatch', 'CrawlResultController@dispatch1');

        //新版抓取路由
        $api->post('/item', 'ItemController@create');
        $api->post('/item/update', 'ItemController@update');
        $api->get('/item', 'ItemController@retrieve');
        $api->post('/item/start', 'ItemController@start');
        $api->post('/item/stop', 'ItemController@stop');
        $api->post('/item/test', 'ItemController@test');

        $api->get('/item/results', 'ItemResultController@allByLast');
        $api->get('/item/test/result', 'ItemTestResultController@getLastTestResult');


    }
);
