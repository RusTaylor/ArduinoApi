# Для начала указываем исходный образ, он будет использован как основа
FROM php:7.2-fpm

# RUN выполняет идущую за ней команду в контексте нашего образа.
# В данном случае мы установим некоторые зависимости и модули PHP.
# Для установки модулей используем команду docker-php-ext-install.
# На каждый RUN создается новый слой в образе, поэтому рекомендуется объединять команды.
RUN apt-get update && apt-get install -y \
    wget  \
    git \
    unzip  \
    curl \
    libcurl3-dev \
    libgd3 \
    libgd-dev \
    libpq-dev

RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql

RUN docker-php-ext-install -j$(nproc) iconv mbstring  pdo pdo_pgsql zip gd json curl \
    && pecl install xdebug-2.6.1 \
    && docker-php-ext-enable xdebug

# Куда же без composer'а.
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Добавим свой php.ini, можем в нем определять свои значения конфига
ADD php.ini /usr/local/etc/php/conf.d/40-custom.ini

# Add user for laravel application
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

# Copy existing application directory permissions
COPY --chown=www:www . /var/www

# Change current user to www
USER www
# Supervisor
#RUN apt-get update -y && apt-get install -y supervisor \
#    && rm -rf /var/lib/apt/lists/* \
#    && apt-get clean -y
#ADD supervisord.conf /etc/supervisor/conf.d/custom.conf

# Указываем рабочую директорию для PHP
WORKDIR /app

# Запускаем контейнер
# Из документации: The main purpose of a CMD is to provide defaults for an executing container. These defaults can include an executable,
# or they can omit the executable, in which case you must specify an ENTRYPOINT instruction as well.

#CMD ["/usr/bin/supervisord"]
