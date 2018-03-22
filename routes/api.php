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
    ['namespace' => 'App\Http\Controllers\Api\V1', 'prefix'=>'v1'],
    function (Dingo\Api\Routing\Router $api) {
        $api->get('/test', function(Illuminate\Http\Request $request){
            return 'test';
        });
        $api->post('/crawl/task', 'CrawlTaskController@create');
        $api->post('/crawl/task/test', 'CrawlTaskController@test');
        $api->post('/crawl/task/stop', 'CrawlTaskController@stop');
        $api->post('/crawl/task/start','CrawlTaskController@start');
        $api->get('/crawl/tasks','CrawlTaskController@all');
        $api->get('/crawl/tasks/ids','CrawlTaskController@listByIds');
        $api->get('/crawl/task/queue/name', 'CrawlTaskController@getByQueueName');
        $api->get('/crawl/task/queue/info', 'CrawlTaskController@getQueueInfo');
        $api->post('/crawl/results', 'CrawlResultController@createForBatch');
    }
);
