<?php
namespace app\controller;

use app\BaseController;
use think\facade\Db;
use think\facade\Cache;

class Index extends BaseController
{
    public function index()
    {
        // 测试数据库
        $product = Db::name('product')->find(1);

        // 测试 Redis
        Cache::set('test_key', 'redis_ok', 60);
        $redisVal = Cache::get('test_key');

        return json([
            'code' => 0,
            'msg' => 'ok',
            'data' => [
                'product' => $product,
                'redis_test' => $redisVal,
            ]
        ]);
    }
}