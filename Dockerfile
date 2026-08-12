# Builds a self-contained image for the Yii 2 landing page app, suitable for any
# container host (Render, Fly.io, Railway, etc.). See render.yaml for the Render
# Blueprint that deploys straight from this file.
#
# Runs php-fpm + nginx (via supervisord) rather than PHP's built-in `php -S` dev
# server — that server is explicitly not meant for production, and in practice its
# single long-running process could end up unable to resolve its own relative
# router.php path after enough uptime, taking the whole site down.

FROM php:8.3-fpm

RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        nginx \
        supervisor \
        gettext-base \
        libicu-dev \
        libzip-dev \
        libpng-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" intl gd zip mbstring ctype iconv \
    && rm -rf /var/lib/apt/lists/* \
    && rm -f /etc/nginx/sites-enabled/default

COPY docker/uploads.ini /usr/local/etc/php/conf.d/uploads.ini
COPY docker/nginx-app.conf.template /etc/nginx/templates/app.conf.template
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Installed separately from the rest of the source so this (slow) layer is only
# rebuilt when composer.json/composer.lock actually change.
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

COPY . .
RUN composer dump-autoload --optimize \
    && mkdir -p runtime web/assets \
    && chmod -R 777 runtime web/assets

# Production defaults — override YII_DEBUG/YII_ENV via the platform's env vars if
# ever needed, per web/index.php.
ENV YII_ENV=prod
ENV YII_DEBUG=0

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["supervisord", "-n", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
