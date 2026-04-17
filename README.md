# order-demo

基于 ThinkPHP 8 实现的订单创建接口 Demo，重点演示接口幂等性处理方案。

## 功能

- 订单创建接口（`POST /order/create`）
- 通过 `request_id` 实现幂等控制，防止重复下单
- 唯一索引兜底并发场景，保证数据一致性
- 库存扣减与并发冲突回滚

## 接口说明

### POST /order/create

| 参数 | 类型 | 说明 |
|------|------|------|
| user_id | int | 用户ID |
| product_id | int | 商品ID |
| request_id | string | 幂等键，必传，全局唯一 |

**响应示例**
```json
{
  "code": 0,
  "msg": "下单成功",
  "data": {
    "order_id": 1,
    "order_no": "20240418120000123456"
  }
}
```

## 环境要求

- PHP 8.0+
- MySQL 5.7+
- Composer

## 安装

```bash
composer install
cp .example.env .env
# 编辑 .env 填写数据库配置
```

## 启动

```bash
php think run
```

访问 `http://localhost:8000`
