<?php
namespace app\controller;

use app\BaseController;
use think\facade\Db;

class Admin extends BaseController
{
    public function product()
    {
        $products = Db::name('product')->order('id', 'asc')->select();
        return view('admin/product', [
            'menu'     => 'product',
            'products' => $products,
            'count'    => $products->count(),
        ]);
    }

    public function dashboard()
    {
        // 总商品数
        $count = Db::name('product')->count();
        // 总库存
        $stock = Db::name('product')->sum('stock');
        // 总货值（用原生 SQL 计算 price * stock）
        $result = Db::query('SELECT SUM(price * stock) AS total_value FROM product');
        $value  = $result[0]['total_value'] ?? 0;

        // 低库存预警（库存 < 20）
        $lowStock = Db::name('product')
            ->where('stock', '<', 20)
            ->order('stock', 'asc')
            ->select();

        // 最近销售订单
        $recentOrders = Db::name('order')
            ->order('id', 'desc')
            ->limit(5)
            ->select();

        return view('admin/dashboard', [
            'menu'          => 'dashboard',
            'count'         => $count,
            'stock'         => $stock,
            'value'         => $value,
            'lowStock'      => $lowStock,
            'recentOrders'  => $recentOrders,
        ]);
    }
}
