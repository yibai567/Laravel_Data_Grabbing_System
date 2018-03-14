<?php
/**
 * Created by PhpStorm.
 * User: Pascal
 * Date: 2017/12/8
 * Time: 下午2:15
 */

return [
    'corp_id' => env('WEWORK_CORP_ID', 'ww03d46c41f59ca02e'),
    'corp_secret' => env('WEWORK_CORP_SECRET', '43Y3Ze3eEea0DCFEMYaIkMcTPAHQ9qBWIlZNl-cGrYM'),
    'agent_id' => env('WEWORK_AGENT_ID', '1000005'),
    'corp_txl_secret' => env('WEWORK_CORP_TXL_SECRET', 'bXn5BqQYLNfrK_BeNlvIZkewfa_WpPwG1Q7WGBb-_tw'),
    'corp_app_config' => [
        [
            "app_desc" => "金色财经应用",
            "agent_id" => 1000005,
            "secret" => '43Y3Ze3eEea0DCFEMYaIkMcTPAHQ9qBWIlZNl-cGrYM',
            "token" => "wework",
            "encoding_aes_key" => "",
        ],
    ],
    'notice_manager' => env('WEWORK_NOTICE_MANAGER', 'yuwenbin@jinse.com'),
];