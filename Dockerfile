FROM php:fpm AS rssbridge

LABEL description="RSS-Bridge is a PHP project capable of generating RSS and Atom feeds for websites that don't have one."
LABEL repository="https://github.com/RSS-Bridge/rss-bridge"
LABEL website="https://github.com/RSS-Bridge/rss-bridge"

ARG DEBIAN_FRONTEND=noninteractive
RUN apt-get update && \
    apt-get install --yes --no-install-recommends \
      ca-certificates \
      nginx \
      zlib1g-dev \
      libzip-dev \
      nss-plugin-pem \
      libicu-dev && \
    docker-php-ext-install zip && \
    docker-php-ext-install intl && \
    mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# logs should go to stdout / stderr
RUN ln -sfT /dev/stderr /var/log/nginx/error.log; \
	ln -sfT /dev/stdout /var/log/nginx/access.log; \
	chown -R --no-dereference www-data:adm /var/log/nginx/
COPY ./config/nginx.conf /etc/nginx/sites-available/default
COPY ./config/php-fpm.conf /etc/php/8.2/fpm/pool.d/rss-bridge.conf
COPY ./config/php.ini /etc/php/8.2/fpm/conf.d/90-rss-bridge.ini

COPY --chown=www-data:www-data ./ /app/

EXPOSE 80

ENTRYPOINT ["/app/docker-entrypoint.sh"]
