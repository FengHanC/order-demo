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

    public function edit()
    {
        $id = (int) $this->request->param('id');
        $product = Db::name('product')->find($id);
        if (!$product) {
            return redirect('/admin/product');
        }
        $prev = Db::name('product')->where('id', '<', $id)->order('id', 'desc')->find();
        $next = Db::name('product')->where('id', '>', $id)->order('id', 'asc')->find();

        return view('admin/edit', [
            'menu'    => 'product',
            'p'       => $product,
            'prev'    => $prev,
            'next'    => $next,
        ]);
    }

    public function order()
    {
        $orders = Db::query('
            SELECT o.*, p.name AS product_name
            FROM `order` o
            LEFT JOIN product p ON o.product_id = p.id
            ORDER BY o.id DESC
        ');

        return view('admin/order', [
            'menu'   => 'order',
            'orders' => $orders,
            'count'  => count($orders),
        ]);
    }

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

    // ---- 出入库 ----

    public function warehouse()
    {
        $records = Db::query('
            SELECT w.*, p.name AS product_name
            FROM warehouse_record w
            LEFT JOIN product p ON w.product_id = p.id
            ORDER BY w.id DESC
        ');

        $stats = Db::query('
            SELECT
                SUM(CASE WHEN type=1 THEN quantity ELSE 0 END) AS total_in,
                SUM(CASE WHEN type=2 THEN quantity ELSE 0 END) AS total_out
            FROM warehouse_record
        ');

        return view('admin/warehouse', [
            'menu'     => 'warehouse',
            'records'  => $records,
            'count'    => count($records),
            'totalIn'  => $stats[0]['total_in'] ?? 0,
            'totalOut' => $stats[0]['total_out'] ?? 0,
        ]);
    }

    public function stockIn(Request $request)
    {
        if ($request->isPost()) {
            $productId = (int) $request->param('product_id');
            $quantity  = (int) $request->param('quantity', 0);
            $remark    = trim($request->param('remark', ''));

            if ($productId <= 0 || $quantity <= 0) {
                return redirect('/admin/stockIn');
            }

            $product = Db::name('product')->find($productId);
            if (!$product) {
                return redirect('/admin/stockIn');
            }

            Db::startTrans();
            try {
                Db::name('warehouse_record')->insert([
                    'product_id' => $productId,
                    'type'       => 1,
                    'quantity'   => $quantity,
                    'remark'     => $remark,
                    'created_at' => time(),
                ]);
                Db::name('product')->where('id', $productId)->inc('stock', $quantity)->update();
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
            }

            return redirect('/admin/warehouse');
        }

        $products = Db::name('product')->order('id', 'asc')->select();
        return view('admin/stock_in', [
            'menu'     => 'warehouse',
            'products' => $products,
        ]);
    }

    public function stockOut(Request $request)
    {
        $error = $request->param('error') == 1;
        if ($request->isPost()) {
            $productId = (int) $request->param('product_id');
            $quantity  = (int) $request->param('quantity', 0);
            $remark    = trim($request->param('remark', ''));

            if ($productId <= 0 || $quantity <= 0) {
                return redirect('/admin/stockOut');
            }

            $product = Db::name('product')->find($productId);
            if (!$product || $product['stock'] < $quantity) {
                $error = true;
                $products = Db::name('product')->order('id', 'asc')->select();
                return view('admin/stock_out', [
                    'menu'     => 'warehouse',
                    'products' => $products,
                    'error'    => true,
                ]);
            }

            Db::startTrans();
            try {
                Db::name('warehouse_record')->insert([
                    'product_id' => $productId,
                    'type'       => 2,
                    'quantity'   => $quantity,
                    'remark'     => $remark,
                    'created_at' => time(),
                ]);
                Db::name('product')->where('id', $productId)->dec('stock', $quantity)->update();
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
            }

            return redirect('/admin/warehouse');
        }

        $products = Db::name('product')->order('id', 'asc')->select();
        return view('admin/stock_out', [
            'menu'     => 'warehouse',
            'products' => $products,
            'error'    => $error,
        ]);
    }
}
