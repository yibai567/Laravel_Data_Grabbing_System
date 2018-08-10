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

Auth::routes();
Route::group(
    ['middleware' => 'auth'],
    function () {
        Route::get('/wx/room/message/old', 'WWW\WxMessageController@index');
        Route::get('/ajax/wx/message', 'WWW\WxMessageController@ajaxWxMessageList');
        Route::get('/ajax/update/group/status', 'WWW\WxMessageController@ajaxUpdateGroupStatus');
        Route::get('/ajax/update/status', 'WWW\WxMessageController@ajaxUpdateStatus');

        Route::get('/', 'WWW\BlockNewsController@all');
        Route::get('/wx/room/message', 'WWW\WxMessageController@newIndex');
        Route::get('/wx/ajax/room/message/{id}', 'WWW\WxMessageController@ajaxMessageList');
        Route::get('/wx/down/room/message/{id}', 'WWW\WxMessageController@downloadMessageList');
        Route::get('/wx/room/message/text', 'WWW\WxMessageController@text');

        Route::get('/news', 'WWW\BlockNewsController@all');
        Route::get('/block_news', 'WWW\BlockNewsController@index');
        Route::get('/ajax_block_news', 'WWW\BlockNewsController@ajaxList');

        Route::get('/home', 'WWW\HomeController@index');
    }
);
Route::get('/logout', function(){
    Auth::logout();
    return redirect('/login');
});

