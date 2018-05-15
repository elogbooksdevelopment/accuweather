FROM ubuntu:16.04

RUN apt-get update
RUN apt-get upgrade -y
RUN apt-get install -y software-properties-common

RUN LC_ALL=C.UTF-8 add-apt-repository ppa:ondrej/php
RUN apt-get update
RUN apt-get upgrade -y
RUN apt-get install -y \
        nginx-extras \
        php7.2 \
        php7.2-cli \
        php7.2-redis \
        php7.2-fpm \
        git \
        wget
RUN php -r "copy('https://getcomposer.org/installer', 'installer');" \
    && php installer --quiet --install-dir=/bin --filename=composer \
    && rm installer
RUN rm -f /etc/nginx/sites-enabled/* \
    && rm -f /etc/nginx/sites-available/*
COPY accuweather.nginx /etc/nginx/sites-available/accuweather.nginx
RUN ln -s /etc/nginx/sites-available/accuweather.nginx /etc/nginx/sites-enabled/accuweather.nginx
RUN mkdir -p /var/www && rm -rf /var/www/*
RUN git clone https://github.com/six-paths/accuweather /var/www
RUN cp /var/www/config.json.dist /var/www/config.json
RUN chmod 660 /var/www/*
RUN chmod 664 /var/www/app.php

ENTRYPOINT tail -f -n 10 /var/log/nginx/error.log
