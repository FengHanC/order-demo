FROM php:8.2-fpm-alpine

# 安装系统依赖 + PHP 扩展
RUN apk add --no-cache \
        git \
        unzip \
        libzip-dev \
        $PHPIZE_DEPS \
    && docker-php-ext-install pdo_mysql zip bcmath \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /tmp/* /var/cache/apk/*

# 安装 Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# 复制项目源码
COPY . /var/www/html/

# 安装依赖
RUN mkdir -p runtime && chmod -R 777 runtime && \
    composer install --no-interaction --optimize-autoloader

# 生产模式
CMD ["php-fpm"]
