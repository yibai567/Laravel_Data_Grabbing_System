<?php
use App\AMQP;
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

Route::get('/', function () {
    return response('', 404);
});
Route::get('/block_news', 'WWW\BlockNewsController@index');
Route::get('/ajax_block_news', 'WWW\BlockNewsController@ajaxList');

Route::get('/wx_message', 'WWW\WxMessageController@index');
Route::get('/ajax_wx_message', 'WWW\WxMessageController@ajaxWxMessageList');
Route::get('/ajax_update_group_status', 'WWW\WxMessageController@ajaxUpdateGroupStatus');
Route::get('/ajax_update_status', 'WWW\WxMessageController@ajaxUpdateStatus');

Auth::routes();
Route::get('/home', 'HomeController@index');
Route::get('/logout', function(){
    Auth::logout();
    return redirect('/login');
});

Route::get('test', function() {
    $option = [
        'server' => [
            'host' => config('rabbitmq.host'),
            'port' => config('rabbitmq.port'),
            'login' => config('rabbitmq.login'),
            'password' => config('rabbitmq.password'),
            'vhost' => 'test',
            // 'vhost' => config('rabbitmq.vhost'),
        ],
        'type' => 'workqueue',
        'exchange' => 'error',
        'queue' =>'error'
    ];
    $a = AMQP::get_instance($option);
    $a->publish('asdasd','error');
});
