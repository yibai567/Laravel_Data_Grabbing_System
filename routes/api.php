<?php

$api = app('Dingo\Api\Routing\Router');

$api->version(
    'v1',
    ['namespace' => 'App\Http\Controllers\InternalAPI'],
    function (Dingo\Api\Routing\Router $api) {
        $api->group(['prefix' => 'internal_api'], function($api){
            $api->group(['prefix'=>'basic', 'namespace' => 'Basic'], function($api){
                $api->post('/crawl/task', 'CrawlTaskController@create');
                $api->post('/crawl/task/status','CrawlTaskController@updateStatus');
                $api->post('/crawl/result', 'CrawlResultController@create');
            });
            $api->post('/crawl/task', 'CrawlTaskController@create');
            $api->post('/crawl/task/status','CrawlTaskController@updateStatus');
            $api->post('/crawl/result', 'CrawlResultController@create');
        });
    }
);

$api->version(
    'v1',
    ['namespace' => 'App\Http\Controllers\Api'],
    function (Dingo\Api\Routing\Router $api) {
        $api->group(['prefix'=>'v1', 'namespace' => 'V1'], function($api){
            $api->post('/crawl/task', 'CrawlTaskController@create');
            $api->post('/crawl/task/status','CrawlTaskController@updateStatus');
            $api->post('/crawl/result', 'CrawlResultController@create');
        });
    }
);
