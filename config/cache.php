<?php

return [
    // 默认缓存驱动,从 env 读取
    'default' => env('cache.driver', 'file'),

    // 缓存连接方式配置
    'stores'  => [
        'file' => [
            'type'       => 'File',
            'path'       => '',
            'prefix'     => '',
            'expire'     => 0,
            'tag_prefix' => 'tag:',
            'serialize'  => [],
        ],
        'redis' => [
            'type'       => 'redis',
            'host'       => env('redis.host', '127.0.0.1'),
            'port'       => env('redis.port', 6379),
            'password'   => env('redis.password', ''),
            'select'     => env('redis.select', 0),
            'timeout'    => 0,
            'expire'     => 0,
            'persistent' => false,
            'prefix'     => env('redis.prefix', 'order_demo:'),
            'tag_prefix' => 'tag:',
            'serialize'  => [],
        ],
    ],
];