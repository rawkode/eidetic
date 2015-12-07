#!/bin/bash
echo ""
echo ""
echo "======================================================================";
echo "=== FRESH TEST RUN"
echo "======================================================================";
echo ""
echo ""

if [[ ! -f 'composer.phar' ]];
then
    php -r "readfile('https://getcomposer.org/installer');" | php
fi

# If we're inside our Docker environment, we'll need some extensions
command -v docker-php-ext-install && {
    # Only install the PostgreSQL driver libraries if we need them
    case $DATABASE_DRIVER in
        pdo_pgsql)
            DEBIAN_FRONTEND=noninteractive apt-get update > /dev/null 2>&1 \
                && apt-get install -y -f --no-install-recommends libpq-dev > /dev/null 2>&1
        ;;
    esac

    docker-php-ext-install pdo pdo_mysql pdo_pgsql >/dev/null 2>&1
}

php composer.phar install

bin/phpunit || exit -1
bin/phpspec run --format=pretty || exit -1
