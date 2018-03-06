<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

//创建抓取任务接口
Route::prefix('crawl')->namespace('Crawl')->group(function(){
    Route::post('task', 'CrawlTaskController@create');
    Route::post('task', 'CrawlTaskController@create');
    Route::post('response/send', 'CrawlResponseController@send');
});
