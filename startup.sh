#!bin/bash

for i in $(compgen -e); do
    if [[ $i = "ACCUWEATHER_"* ]]; then
        echo "env[${i}]=\"${!i}\"" >> /etc/php/7.2/fpm/pool.d/www.conf
    fi
done;

service nginx start
service php7.2-fpm start
