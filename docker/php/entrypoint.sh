#!/bin/sh
set -e

# 首次启动时安装 Composer 依赖（如果 vendor 不存在）
if [ ! -d "vendor" ]; then
    echo "→ Installing Composer dependencies..."
    composer install --no-interaction --optimize-autoloader
fi

# 给 runtime 目录写权限
chmod -R 777 runtime 2>/dev/null || true

exec "$@"
