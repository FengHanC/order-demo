<?php
namespace app\controller;

use app\BaseController;
use think\facade\Db;
use think\Request;

class Order extends BaseController
{
    public function create(Request $request)
    {
        $userId    = (int) $request->param('user_id', 1);
        $productId = (int) $request->param('product_id', 1);
        $requestId = trim($request->param('request_id', ''));

        // 🔴 1. 参数校验:request_id 必传
        if (empty($requestId)) {
            return json(['code' => 1, 'msg' => 'request_id 不能为空']);
        }

        // 🔴 2. 幂等查询:如果这个 request_id 已经下过单,直接返回之前的订单
        $existOrder = Db::name('order')->where('request_id', $requestId)->find();
        if ($existOrder) {
            return json([
                'code' => 0,
                'msg'  => '下单成功(幂等命中)',
                'data' => [
                    'order_id' => $existOrder['id'],
                    'order_no' => $existOrder['order_no'],
                ]
            ]);
        }

        // 3. 查商品
        $product = Db::name('product')->find($productId);
        if (!$product) {
            return json(['code' => 1, 'msg' => '商品不存在']);
        }

        if ($product['stock'] <= 0) {
            return json(['code' => 1, 'msg' => '库存不足']);
        }

        // 4. 扣库存
        Db::name('product')->where('id', $productId)->dec('stock')->update();

        // 5. 生成订单号
        $orderNo = date('YmdHis') . mt_rand(100000, 999999);

        // 🔴 6. 插入订单,用 try-catch 兜底唯一索引冲突
        try {
            $orderId = Db::name('order')->insertGetId([
                'order_no'   => $orderNo,
                'user_id'    => $userId,
                'request_id' => $requestId,
                'product_id' => $productId,
                'amount'     => $product['price'],
                'status'     => 0,
                'created_at' => time(),
            ]);

            return json([
                'code' => 0,
                'msg'  => '下单成功',
                'data' => [
                    'order_id' => $orderId,
                    'order_no' => $orderNo,
                ]
            ]);

        } catch (\Exception $e) {
            // 🔴 7. 兜底:唯一索引冲突说明并发下第二个请求抢进来了
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                // 回滚库存(我们刚才扣了 1)
                Db::name('product')->where('id', $productId)->inc('stock')->update();

                // 查出已存在的那条订单返回
                $existOrder = Db::name('order')->where('request_id', $requestId)->find();
                return json([
                    'code' => 0,
                    'msg'  => '下单成功(并发兜底)',
                    'data' => [
                        'order_id' => $existOrder['id'],
                        'order_no' => $existOrder['order_no'],
                    ]
                ]);
            }
            throw $e;
        }
    }
}