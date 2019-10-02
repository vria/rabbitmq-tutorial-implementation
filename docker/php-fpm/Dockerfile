FROM alpine:3.6

ENV PHP_VERSION=7.2.21 \
    PHP_AMQP_VERSION=1.9.0

RUN apk upgrade --update --no-cache && \
    apk add --update --no-cache \
    ca-certificates \
    curl \
    bash

RUN apk add --update --no-cache \
    coreutils \
    php7-fpm \
    php7-apcu \
    php7-ctype \
    php7-curl \
    php7-dom \
    php7-gd \
    php7-iconv \
    php7-imagick \
    php7-json \
    php7-intl \
    php7-mcrypt \
    php7-fileinfo\
    php7-mbstring \
    php7-opcache \
    php7-openssl \
    php7-pdo \
    php7-pdo_mysql \
    php7-mysqli \
    php7-xml \
    php7-zlib \
    php7-phar \
    php7-tokenizer \
    php7-session \
    php7-simplexml \
    php7-xdebug \
    php7-zip \
    php7-xmlwriter \
    php7-bcmath \
    php7-sockets \
    make

RUN echo "$(curl -sS https://composer.github.io/installer.sig) -" > composer-setup.php.sig \
        && curl -sS https://getcomposer.org/installer | tee composer-setup.php | sha384sum -c composer-setup.php.sig \
        && php composer-setup.php && rm composer-setup.php* \
        && chmod +x composer.phar && mv composer.phar /usr/bin/composer

RUN apk add --update --no-cache libmemcached rabbitmq-c

RUN apk add --update --no-cache git file re2c autoconf make g++ php7-dev libmemcached-dev cyrus-sasl-dev zlib-dev musl rabbitmq-c-dev pcre-dev

RUN git clone --depth=1 -b v${PHP_AMQP_VERSION} https://github.com/pdezwart/php-amqp.git /tmp/php-amqp && \
    cd /tmp/php-amqp && \
    phpize && ./configure && make && make install && \
    cd .. && rm -rf /tmp/php-amqp/ && \
    echo 'extension=amqp.so' >> /etc/php7/conf.d/amqp.ini

COPY symfony.ini /etc/php7/conf.d/
COPY symfony.ini /etc/php7/cli/conf.d/
COPY xdebug.ini  /etc/php7/conf.d/

COPY symfony.pool.conf /etc/php7/php-fpm.d/

CMD ["php-fpm7", "-F"]

WORKDIR /var/www/symfony
EXPOSE 9001
