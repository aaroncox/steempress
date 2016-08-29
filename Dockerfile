FROM richarvey/nginx-php-fpm

COPY resources/config/docker/sites-enabled/steempress.conf /etc/nginx/sites-enabled/default.conf

RUN echo http://nl.alpinelinux.org/alpine/edge/testing >> /etc/apk/repositories && apk add --no-cache shadow

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php -r "if (hash_file('SHA384', 'composer-setup.php') === '${composer_hash}') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" && \
    php composer-setup.php --install-dir=/usr/bin --filename=composer && \
    php -r "unlink('composer-setup.php');"

EXPOSE 80

CMD /src/steempress/resources/config/docker/start.sh
