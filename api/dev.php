<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-01-01
 * Time: 20:06
 */

return [
    'SERVER_NAME' => "JianGong",
    'app_name' => "剪工",
    'openssl_key' => "ed86cc2f9eaf4f15f357375848d6f7ee",
    'MAIN_SERVER' => [
        'LISTEN_ADDRESS' => '0.0.0.0',
        'PORT' => 9507,
        'SERVER_TYPE' => EASYSWOOLE_WEB_SERVER, //可选为 EASYSWOOLE_SERVER  EASYSWOOLE_WEB_SERVER EASYSWOOLE_WEB_SOCKET_SERVER
        'SOCK_TYPE' => SWOOLE_TCP,
        'RUN_MODEL' => SWOOLE_PROCESS,
        'SETTING' => [
            'worker_num' => 2,
            'max_request' => 5000,
            'task_worker_num' => 2,
            'task_max_request' => 1000,
            'task_enable_coroutine' => true,//开启后自动在onTask回调中创建协程
        ],
    ],
    'TEMP_DIR' => null,
    'LOG_DIR' => null,
    'CONSOLE' => [
        'ENABLE' => false,
        'LISTEN_ADDRESS' => '127.0.0.1',
        'HOST' => '127.0.0.1',
        'PORT' => 9502,
        'USER' => 'root',
        'PASSWORD' =>'123456'
    ],
    'FAST_CACHE' => [
        'PROCESS_NUM' => 0,
        'BACKLOG' => 256,
    ],
    'DISPLAY_ERROR' => true,
    //  mysql配置
    'MYSQL' => [
        'host'          => '127.0.0.1',
        'port'          => '3306',
        'user'          => 'jg',
        'timeout'       => '5',
        'charset'       => 'utf8mb4',
        'password'      => 'jg20191018170605',
        'database'      => 'jg',
        'POOL_MAX_NUM'  => 8,
        'POOL_TIME_OUT' => '0.1',
    ],
    //  redis配置
    'REDIS' => [
        'host' => '127.0.0.1',
        'port' => '6375',
    ],
    //  阿里云OSS存储
    'ALIOSS' => [
        'bucket' => 'jgoss001',
        'accesskey_id' => 'LTAIsVrdfUG05SYz',
        'accesskey_secret' => '7WGYSeDp4GAAkWoF1IWKYEFcezMLhG',
        'endpoint' => 'http://oss-cn-beijing.aliyuncs.com',
        'visiturl' => 'http://jgoss001.oss-cn-beijing.aliyuncs.com',
    ],
    //  微信支付
    'WXPAY' => [
        'appid' => 'xxxxx',
        'key' => 'xxxxx',
        'mchid' => 'xxxx',
        'paytitle' => '订单支付',
        'debug' => true,
    ],
    //  支付宝支付
    'ALIPAY' => [
        'appid' => 'xxxxx',
        'PrivateKey' => 'xxxxx',
        'PublicKey' => 'xxxxx',
        'paytitle' => '订单支付',
        'debug' => true,
    ],
    //  url配置
    'URL' => [
        'api' => 'http://jg.quan-oo.com', //接口域名
    ],
    //  容联云通讯
    'RONGLIAN' => [
        'accountSid' => 'xxxxx',
        'accountToken' => 'xxxxx',
        'appId' => 'xxxxx',
        'serverIP' => 'app.cloopen.com',
        'serverPort' => '8883',
        'softVersion' => '2013-12-26',
        'tempId' => [
            'xxxxx'
        ],
    ],
    'es' => [
        'host' => 'es.hootinwang.com',
        'port' => '80',
        'scheme' => 'http',
    ]
];
