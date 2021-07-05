#!/bin/bash

# 当前目录
CURRENT_DIR=`pwd`
# 将该文件所在的目录设为工作目录
WORK_DIR=`dirname ${0}`
# 移至工作目录 
cd ${WORK_DIR} || exit 1

# 创建容器（首次）
docker-compose up --no-start

# 启动容器
docker-compose start

exit 1
