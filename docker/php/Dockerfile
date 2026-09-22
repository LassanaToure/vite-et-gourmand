FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libssl-dev pkg-config \
    && docker-php-ext-install pdo_mysql \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && a2enmod rewrite headers \
    && cp "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini" \
    && rm -rf /var/lib/apt/lists/* /tmp/pear

COPY docker/php/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

WORKDIR /var/www/html
COPY . .

ENTRYPOINT ["entrypoint.sh"]
