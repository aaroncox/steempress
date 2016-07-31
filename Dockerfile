FROM richarvey/nginx-php-fpm

COPY resources/config/docker/sites-enabled/steempress.conf /etc/nginx/sites-enabled/default.conf

RUN echo http://nl.alpinelinux.org/alpine/edge/testing >> /etc/apk/repositories && apk add --no-cache shadow

EXPOSE 80

CMD /src/steempress/resources/config/docker/start.sh
