#!/bin/bash

# 当前目录
CURRENT_DIR=`pwd`
# 将该文件所在的目录设为工作目录
WORK_DIR=`dirname ${0}`
# 移至工作目录 
cd ${WORK_DIR} || exit 1

# 复制配置文件
cp env/env.dev ../.env

# 创建容器（首次）
docker-compose up --no-start

# 启动容器
docker-compose start

docker-compose exec api_test sh -c "composer config -g repo.packagist composer https://mirrors.aliyun.com/composer"
docker-compose exec api_test sh -c "cd /data/www/apitest && composer install"
docker-compose exec api_test sh -c "cd /data/www/apitest && php artisan apitest:install"

exit 1
