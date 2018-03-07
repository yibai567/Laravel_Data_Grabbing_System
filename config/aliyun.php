<?php

return [
    'access_key' => env('ALIYUN_ACCESS_KEY', 'LTAIjgPYDSbHINO3'),
    'access_secret' => env('ALIYUN_ACCESS_SECRET', 'ZrC9N1IhJlEjqIdzUITM8gzhimSfos'),
    'search_app_name' => env('OPEN_SEARCH_APP_NAME', 'jinse_dev_search'),
    'search_host' => env('OPEN_SEARCH_HOST', 'http://opensearch-cn-beijing.aliyuncs.com'),
    'oss' => [
        'domain' => env('OSS_DOMAIN', 'jinse-develop.oss-cn-shanghai.aliyuncs.com'),
        'scheme' => env('OSS_SCHEME', 'http://'),
        'production_host' => env('OSS_PRODUCTION_HOST', 'oss-cn-shanghai.aliyuncs.com'),
        'internal_host' => env('OSS_INTERNAL_HOST', 'oss-cn-shanghai.aliyuncs.com'),
        'base_key' => env('OSS_BASE_KEY', ''),
        'bucket' => env('OSS_BUCKET', 'jinse-develop'),
        'bucket_private' => env('OSS_BUCKET_PRIVATE', 'jinse-private'),
        'audio_directory' => 'audio',
        'separator' => '_',
        /*
         * 热门新闻，image7：340 * 188
         * 相关新闻，image8：241 * 148
         * 热门推荐，image9：130 * 81
         * 头条新闻大，image10：530 * 330
         * 头条新闻小，image11：260 * 160
         * 首页头条新闻大，image12：800 * 300
         * 首页头条新闻小，image13：260 * 140
         */
        'thum' => [
            'list' => 'image1.png',
            'head' => 'image2.png',
            'content' => 'image3.png',
            'hot' => 'image4.png',
            'mHead' => 'image5.png',
            'mList' => 'image6.png',
            'newHot' => 'image7.png',
            'newRelate' => 'image8.png',
            'newRecommend' => 'image9.png',
            'newHeadBig' => 'image10.png',
            'newHeadSmall' => 'image11.png',
            'newIndexHeadBig' => 'image12.png',
            'newIndexHeadSmall' => 'image13.png',
            'avatar' => 'image20.png',

            /*
             * 比特币新版今日头条图大, image101: 340 * 234
             * 比特币新版今日头条图小, image102: 165 * 114
             * 比特币新版子栏目模块头条图, image103: 380 * 180
             * 比特币新版子栏目模块列表缩列图, image104: 180 * 111
             * 比特币其他数字货币圆形缩列图, image105: 70 * 70
             */
            'bitcoinNewsTopBig' => 'image101.png',
            'bitcoinNewsTopSmall' => 'image102.png',
            'bitcoinNewsSubCateTop' => 'image103.png',
            'bitcoinNewsSubCateThum' => 'image104.png',
            'bitcoinNewsSubCateSmallThum' => 'image105.png',

        ],
    ],
    'sms' => [
        'endpoint' => 'cn-hangzhou',
        'sign' => '金色财经',
        'template' => [
            'test' => 'SMS_41085020',
            'forget' => 'SMS_41540070',
            'register' => 'SMS_113025022',
            'newsflish' => 'SMS_43320017',
            'market' => 'SMS_43330049',
            'exception' => 'SMS_43335021',
            'bind' => 'SMS_58920229',
            'login' => 'SMS_113020020',
            'bind2' => 'SMS_112895024',
            'security' => 'SMS_112925023'
        ],
    ],
];