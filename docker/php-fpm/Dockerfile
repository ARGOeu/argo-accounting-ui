FROM gitlab-registry.in2p3.fr/cc-in2p3-devops/openshift-origin/openshift-images/php:8.1-fpm-alpine
LABEL maintainer="CNRS/CCIN2P3 DevOps Team"

RUN apk add git
RUN apk add --no-cache --update --virtual buildDeps autoconf
RUN apk add --no-cache --virtual .php-build zlib-dev  libpng-dev libjpeg-turbo-dev freetype-dev php-sqlite3

RUN pecl install raphf propro
RUN docker-php-ext-enable raphf propro
RUN pecl install pecl_http
RUN echo -e "extension=raphf.so\nextension=propro.so\nextension=iconv.so\nextension=http.so" > /usr/local/etc/php/conf.d/docker-php-ext-http.ini
RUN rm -rf /usr/local/etc/php/conf.d/docker-php-ext-raphf.ini
RUN rm -rf /usr/local/etc/php/conf.d/docker-php-ext-propro.ini
RUN rm -rf /tmp/*


WORKDIR /var/www/html
ADD . /var/www/html

RUN mv .env.template .env



RUN /usr/local/bin/php composer.phar install
RUN yarn install --ignore-engines \
    && yarn encore prod ;

