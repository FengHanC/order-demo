-- order_demo 数据库初始化
-- 订单表
CREATE TABLE IF NOT EXISTS `order` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_no`   VARCHAR(64)     NOT NULL DEFAULT '' COMMENT '订单号',
    `user_id`    INT UNSIGNED    NOT NULL DEFAULT 0  COMMENT '用户 ID',
    `request_id` VARCHAR(128)    NOT NULL DEFAULT '' COMMENT '幂等请求 ID',
    `product_id` INT UNSIGNED    NOT NULL DEFAULT 0  COMMENT '商品 ID',
    `amount`     DECIMAL(10,2)   NOT NULL DEFAULT 0  COMMENT '金额',
    `status`     TINYINT         NOT NULL DEFAULT 0  COMMENT '状态：0-待支付',
    `created_at` INT UNSIGNED    NOT NULL DEFAULT 0  COMMENT '创建时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_request_id` (`request_id`) COMMENT '幂等唯一索引',
    KEY `idx_user_id` (`user_id`),
    KEY `idx_order_no` (`order_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='订单表';

-- 商品表
CREATE TABLE IF NOT EXISTS `product` (
    `id`         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(128)    NOT NULL DEFAULT '' COMMENT '商品名',
    `price`      DECIMAL(10,2)   NOT NULL DEFAULT 0  COMMENT '价格',
    `stock`      INT UNSIGNED    NOT NULL DEFAULT 0  COMMENT '库存',
    `created_at` INT UNSIGNED    NOT NULL DEFAULT 0  COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商品表';

-- 初始化测试商品
INSERT INTO `product` (`id`, `name`, `price`, `stock`, `created_at`) VALUES
    (1, '测试商品A', 99.90, 100, UNIX_TIMESTAMP()),
    (2, '测试商品B', 199.00, 50, UNIX_TIMESTAMP());
