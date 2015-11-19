#!/bin/bash
if [[ ! -f bin/php-cs-fixer ]];
then
    echo "Have you forgotten todo a 'composer install' first?"
    exit
fi

echo "Running php-cs-fixer with --level=psr2 ..."
php bin/php-cs-fixer fix src --level=psr2

while read fixer; do
    echo "Running php-cs-fixer with --fixers $fixer ..."
    php bin/php-cs-fixer fix src --fixers=$fixer
done < .php-cs-fixers

