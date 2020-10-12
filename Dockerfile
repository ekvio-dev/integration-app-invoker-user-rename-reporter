####
FROM php:7.4.2-cli-alpine
LABEL Description="Typical user rename reporter"
LABEL Maintainer="Maxim Borodavka borodavka.maxim@e-queo.com"
ENV BuildTimezone Europe/Moscow
ENV AppUserName app
ENV AppUID 1200
ENV AppGID 1100
RUN mkdir /${AppUserName}
WORKDIR /${AppUserName}
COPY composer.json composer.lock ./
RUN \
# add system dependencies required for build
    apk add --no-cache --virtual build-deps \
        autoconf \
        gcc \
        make \
        g++ \
        php7-mbstring && \
# download and install composer
    curl -s -f -L -o /tmp/composer-setup.php https://getcomposer.org/installer && \
    EXPECTED_CHECKSUM="$(wget -q -O - https://composer.github.io/installer.sig)" && \
    ACTUAL_CHECKSUM="$(php -r "echo hash_file('sha384', '/tmp/composer-setup.php');")" && \
    if [ "$EXPECTED_CHECKSUM" != "$ACTUAL_CHECKSUM" ]; then >&2 echo 'ERROR: Invalid installer checksum' && \
    rm /tmp/composer-setup.php && \
    exit 1; fi && \
    php /tmp/composer-setup.php --no-ansi --install-dir=/usr/local/bin --filename=composer && \
    composer --ansi --version --no-interaction && \
    rm -f /tmp/*.php && \
# install composer dependencies
    composer install --prefer-dist --no-dev --no-progress --no-cache && \
# remove system dependencies required for build
    apk del build-deps && \
# install system persistent dependencies
    apk add --no-cache --virtual persistent-deps \
        shadow \
        tzdata && \
    groupadd --gid ${AppGID} ${AppUserName} && \
    useradd --home /${AppUserName} --no-create-home --uid ${AppUID} --gid ${AppGID} --shell /bin/sh ${AppUserName} && \
    chown -R ${AppUserName}:${AppUserName} /${AppUserName} && \
    cp /usr/share/zoneinfo/${BuildTimezone} /etc/localtime && \
    echo ${BuildTimezone} > /etc/timezone && \
    mkfifo /tmp/stdout && \
    chmod 777 /tmp/stdout && \
    rm composer.json composer.lock
#create application files
COPY *.php ./
# create custom php config
COPY .ops/conf/php/custom.ini /usr/local/etc/php/conf.d/custom.ini
WORKDIR /${AppUserName}       
CMD ["sh", "-c", "php /${AppUserName}/index.php"]
