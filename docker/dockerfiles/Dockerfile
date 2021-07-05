FROM debian:buster

# Let the container know that there is no tty
ENV DEBIAN_FRONTEND noninteractive
ENV NGINX_VERSION 1.17.3-1~buster
ENV php_conf /etc/php/7.3/fpm/php.ini
ENV fpm_conf /etc/php/7.3/fpm/pool.d/www.conf
ENV COMPOSER_VERSION 1.10.15
ENV LANG C.UTF-8

# Install Basic Requirements
RUN buildDeps='curl gcc make autoconf libc-dev zlib1g-dev pkg-config' \
    && set -x \
    && apt-get update \
    && apt-get install --no-install-recommends $buildDeps --no-install-suggests -q -y gnupg2 dirmngr wget apt-transport-https lsb-release ca-certificates apt-utils \
    && curl -fsSL https://nginx.org/keys/nginx_signing.key | apt-key add - \
    && echo "deb http://nginx.org/packages/mainline/debian `lsb_release -cs` nginx" > /etc/apt/sources.list.d/nginx.list \
    && wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg \
    && echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list \
    && apt-get update \
    && apt-get install --no-install-recommends --no-install-suggests -q -y \
            apt-utils \
            nano \
            vim \
            zip \
            unzip \
            libgmp-dev \
            python-pip \
            python-setuptools \
            git \
            libmagickwand-dev \
            nginx=${NGINX_VERSION} \
            php7.3-fpm \
            php7.3-cli \
            php7.3-bcmath \
            php7.3-gmp \
            php7.3-dev \
            php7.3-common \
            php7.3-opcache \
            php7.3-mbstring \
            php7.3-curl \
            php7.3-mysql \
            php7.3-zip \
            php7.3-xml \
            php-pear \
            cron \
    && pecl -d php_suffix=7.3 install -o -f apcu-5.1.17 \
    && pecl -d php_suffix=7.3 install -o -f msgpack-2.0.3 \
    && pecl -d php_suffix=7.3 install -o -f redis-5.0.2 \
    && pecl -d php_suffix=7.3 install -o -f xdebug \
    && mkdir -p /run/php \
    && pip install wheel \
    && pip install supervisor supervisor-stdout \
    && echo "#!/bin/sh\nexit 0" > /usr/sbin/policy-rc.d \
    && rm -rf /etc/nginx/conf.d/default.conf \
    && sed -i -e "s/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/g" ${php_conf} \
    && sed -i -e "s/memory_limit\s*=\s*.*/memory_limit = 256M/g" ${php_conf} \
    && sed -i -e "s/upload_max_filesize\s*=\s*2M/upload_max_filesize = 100M/g" ${php_conf} \
    && sed -i -e "s/post_max_size\s*=\s*8M/post_max_size = 100M/g" ${php_conf} \
    && sed -i -e "s/variables_order = \"GPCS\"/variables_order = \"EGPCS\"/g" ${php_conf} \
    && sed -i -e "s/;daemonize\s*=\s*yes/daemonize = no/g" /etc/php/7.3/fpm/php-fpm.conf \
    && sed -i -e "s/;catch_workers_output\s*=\s*yes/catch_workers_output = yes/g" ${fpm_conf} \
    && sed -i -e "s/pm.max_children = 5/pm.max_children = 4/g" ${fpm_conf} \
    && sed -i -e "s/pm.start_servers = 2/pm.start_servers = 3/g" ${fpm_conf} \
    && sed -i -e "s/pm.min_spare_servers = 1/pm.min_spare_servers = 2/g" ${fpm_conf} \
    && sed -i -e "s/pm.max_spare_servers = 3/pm.max_spare_servers = 4/g" ${fpm_conf} \
    && sed -i -e "s/pm.max_requests = 500/pm.max_requests = 200/g" ${fpm_conf} \
    && sed -i -e "s/www-data/nginx/g" ${fpm_conf} \
    && sed -i -e "s/^;clear_env = no$/clear_env = no/" ${fpm_conf} \
    && echo "extension=apcu.so" > /etc/php/7.3/mods-available/apcu.ini \
    && echo "extension=msgpack.so" > /etc/php/7.3/mods-available/msgpack.ini \
    && echo "extension=redis.so" > /etc/php/7.3/mods-available/redis.ini \
    && ln -sf /etc/php/7.3/mods-available/apcu.ini /etc/php/7.3/fpm/conf.d/20-apcu.ini \
    && ln -sf /etc/php/7.3/mods-available/apcu.ini /etc/php/7.3/cli/conf.d/20-apcu.ini \
    && ln -sf /etc/php/7.3/mods-available/msgpack.ini /etc/php/7.3/fpm/conf.d/20-msgpack.ini \
    && ln -sf /etc/php/7.3/mods-available/msgpack.ini /etc/php/7.3/cli/conf.d/20-msgpack.ini \
    && ln -sf /etc/php/7.3/mods-available/redis.ini /etc/php/7.3/fpm/conf.d/20-redis.ini \
    && ln -sf /etc/php/7.3/mods-available/redis.ini /etc/php/7.3/cli/conf.d/20-redis.ini \
    # Install Composer
    && curl -o /tmp/composer-setup.php https://getcomposer.org/installer \
    && curl -o /tmp/composer-setup.sig https://composer.github.io/installer.sig \
    && php -r "if (hash('SHA384', file_get_contents('/tmp/composer-setup.php')) !== trim(file_get_contents('/tmp/composer-setup.sig'))) { unlink('/tmp/composer-setup.php'); echo 'Invalid installer' . PHP_EOL; exit(1); }" \
    && php /tmp/composer-setup.php --no-ansi --install-dir=/usr/local/bin --filename=composer --version=${COMPOSER_VERSION} \
    && rm -rf /tmp/composer-setup.php \
    # Clean up
    && rm -rf /tmp/pear \
    && apt-get purge -y --auto-remove $buildDeps \
    && apt-get clean \
    && apt-get autoremove \
    && rm -rf /var/lib/apt/lists/* \
    && ln -sf /usr/share/zoneinfo/Asia/Shanghai /etc/localtime \
    && echo 'Asia/Shanghai' >/etc/timezone

# Supervisor config
COPY supervisord.conf /etc/supervisord.conf

RUN apt-get update \
    && apt-get install --no-install-recommends --no-install-suggests -q -y php7.3-dev \
    && apt-get install -y g++

RUN echo "[XDebug]\nzend_extension=xdebug.so\nxdebug.mode=debug\nxdebug.start_with_request=yes\nxdebug.client_host=host.docker.internal\nxdebug.client_port=9003\nxdebug.idekey=VSCODE\nxdebug.var_display_max_depth=6\nxdebug.log=/var/log/xdebug.log" > /etc/php/7.3/mods-available/xdebug.ini \
    && ln -sf /etc/php/7.3/mods-available/xdebug.ini /etc/php/7.3/fpm/conf.d/20-xdebug.ini \
    && ln -sf /etc/php/7.3/mods-available/xdebug.ini /etc/php/7.3/cli/conf.d/20-xdebug.ini

# Add Scripts
COPY start.sh  /start.sh

RUN mkdir -p /usr/local/etc/php-fpm.d \
    && mkdir -p /etc/nginx/logs \
    && mkdir -p /usr/local/etc/php/conf.d

RUN echo "[opcache]\nopcache.enable=1\nopcache.enable_cli=1\nopcache.memory_consumption=128\nopcache.interned_strings_buffer=8\nopcache.max_accelerated_files=10000\nopcache.revalidate_freq=3600" > /usr/local/etc/php/conf.d/opcache.ini \
    && echo "[www]\npm = dynamic\npm.max_children = 300\npm.start_servers = 60\npm.min_spare_servers = 60\npm.max_spare_servers = 120\naccess.log = /dev/null\nphp_value[fastcgi.logging] = 0" > /usr/local/etc/php-fpm.d/zzz-www.conf \
    && (echo "* * * * * php /data/www/apitest/artisan schedule:run >> /dev/null 2>&1" ; crontab -l )| crontab

EXPOSE 8081

STOPSIGNAL SIGTERM

WORKDIR /data/www/apitest
