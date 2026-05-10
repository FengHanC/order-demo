<?php
namespace app\controller;

use app\BaseController;
use think\facade\Db;
use think\Request;

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
        $count = Db::name('product')->count();
        $stock = Db::name('product')->sum('stock');
        $result = Db::query('SELECT SUM(price * stock) AS total_value FROM product');
        $value  = $result[0]['total_value'] ?? 0;

        $lowStock = Db::name('product')
            ->where('stock', '<', 20)
            ->order('stock', 'asc')
            ->select();

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

    // 编辑表单
    public function edit()
    {
        $id = (int) $this->request->param('id');
        $product = Db::name('product')->find($id);
        if (!$product) {
            return redirect('/admin/product');
        }
        // 获取上一条/下一条 ID 用于导航
        $prev = Db::name('product')->where('id', '<', $id)->order('id', 'desc')->find();
        $next = Db::name('product')->where('id', '>', $id)->order('id', 'asc')->find();

        return view('admin/edit', [
            'menu'    => 'product',
            'p'       => $product,
            'prev'    => $prev,
            'next'    => $next,
        ]);
    }

    // 保存更新
    public function save(Request $request)
    {
        $id    = (int) $request->param('id');
        $name  = trim($request->param('name', ''));
        $price = (float) $request->param('price', 0);
        $stock = (int) $request->param('stock', 0);

        if (empty($name)) {
            return redirect('/admin/edit?id=' . $id);
        }

        Db::name('product')->where('id', $id)->update([
            'name'  => $name,
            'price' => $price,
            'stock' => max(0, $stock),
        ]);

        return redirect('/admin/product');
    }
}
