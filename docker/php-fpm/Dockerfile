FROM gitlab-registry.in2p3.fr/cc-in2p3-devops/openshift-origin/openshift-images/php:8.1-fpm-alpine
LABEL maintainer="CNRS/CCIN2P3 DevOps Team"

RUN apk add git
RUN apk add --no-cache --update --virtual buildDeps autoconf
RUN apk add --no-cache --virtual .php-build zlib-dev  libpng-dev libjpeg-turbo-dev freetype-dev php-sqlite3 php-curl



WORKDIR /var/www/html
ADD . /var/www/html

RUN mv .env.template .env



RUN /usr/local/bin/php composer.phar install --ignore-platform-req=ext-http
RUN yarn install --ignore-engines \
    && yarn encore prod ;

