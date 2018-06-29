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
        $api->post('/item/delete', 'ItemController@delete');
        $api->get('/item', 'ItemController@retrieve');
        $api->post('/item/start', 'ItemController@start');
        $api->post('/item/stop', 'ItemController@stop');
        $api->post('/item/test', 'ItemController@test');

        $api->get('/item/results', 'ItemResultController@allByLast');
        $api->get('/item/test_result', 'ItemResultController@getTestResult');
        $api->post('/item/results/dispatch', 'ItemResultController@dispatchJob');

        $api->get('/queue_info/job','QueueInfoController@getJob');

        $api->post('/block_news/results', 'BlockNewsController@batchCreate');
        $api->get('/script/queue', 'ScriptController@allByQueue');

        $api->post('/data/batch/report', 'DataController@dataResultReport');
        $api->post('/data/results', 'DataController@batchHandle');

        $api->post('/image/upload', 'ImageController@upload');
        //收集资源
        $api->post('/quirement', 'QuirementPoolController@create');
        $api->post('/quirement/update', 'QuirementPoolController@update');
        $api->get('/quirements', 'QuirementPoolController@all');
        $api->get('/quirement', 'QuirementPoolController@retrieve');

        //微信公众号消息
        $api->post('/weixin/message', 'WeiXinMessageController@create');

        //区块链新闻
        $api->get('/block_news','BlockNewsController@all');
        $api->get('/block_news/companies', 'QuirementPoolController@getCompanies');

    }
);

$api->version(
    'v1',
    ['namespace' => 'App\Http\Controllers\API\V2', 'prefix'=>'v2'],
    function (Dingo\Api\Routing\Router $api) {
        $api->get('/test', function (Illuminate\Http\Request $request) {
            return 'test';
        });

        //新版抓取路由
        $api->post('/data/results', 'DataController@batchHandle');

        $api->post('/image/upload', 'ImageController@upload');

        //微信消息
        $api->post('/wx/message','WxMessageController@create');
        $api->post('/wx/message/group/status','WxMessageController@updateGroupStatus');
        $api->get('/wx/message/group','WxMessageController@allGroup');
        $api->post('/wx/message/status','WxMessageController@updateStatus');
        $api->get('/wx/message','WxMessageController@all');

        //微信新消息管理
        $api->post('/wx/room/message','WxMessageController@newCreate');
        $api->get('/wx/room/problem/group','WxMessageController@getGroupProblem');
        $api->get('/wx/room/message/{id}','WxMessageController@getMessageById');

        //行业新闻
        $api->get('/news','BlockNewsController@all');
        // $api->get('/news/{requirement_id}','BlockNewsController@getByRequirementId');
    }
);
