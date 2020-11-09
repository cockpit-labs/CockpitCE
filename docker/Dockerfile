FROM php:7.3.22-apache
ARG COREPACKAGE
ENV COREPACKAGE ${COREPACKAGE}
ARG VIEWPACKAGE
ENV VIEWPACKAGE ${VIEWPACKAGE}
#ARG ADMINTAG
#ENV ADMINTAG ${ADMINTAG:-latest}
ARG TOKEN
ENV TOKEN ${TOKEN:-BdVfyo_eoZ5FXHfqP9DU}

EXPOSE 80
WORKDIR /app

RUN mkdir /www && mkdir -p /symfony/var && mkdir -p /symfony/vendor && mkdir /cache

VOLUME /symfony/var
VOLUME /symfony/vendor

RUN apt-get update -qq \
    && apt-get install -qy \
        gnupg \
        iputils-ping \
        libicu-dev \
        libzip-dev \
        unzip \
        zip \
        jq \
        default-mysql-client \
        libpng-dev \
        zlib1g-dev  \
        wait-for-it \
        sudo \
        vim \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*


# PHP Extensions
RUN	docker-php-ext-install -j$(nproc) intl opcache pdo_mysql mysqli zip gd
RUN pecl install apcu && docker-php-ext-enable apcu
RUN pecl install timecop-beta
COPY conf/php.ini /usr/local/etc/php/conf.d/app.ini
COPY conf/apcu.ini /usr/local/etc/php/conf.d/apcu.ini
COPY conf/timecop.ini /usr/local/etc/php/conf.d/timecop.ini

# Install Symfony binary
RUN curl -sS https://get.symfony.com/cli/installer | bash

# Apache
RUN mv ${HOME}/.symfony/bin/symfony /usr/local/bin/symfony
COPY conf/cockpitce.conf /etc/apache2/sites-available/000-default.conf
COPY conf/apache.conf /etc/apache2/conf-available/app.conf

RUN a2enconf app
RUN a2enmod rewrite remoteip
RUN a2disconf other-vhosts-access-log

COPY conf/start.sh /

COPY conf/sudoers.d/99_www-data /etc/sudoers.d/

RUN chown www-data:www-data /app
RUN chown www-data:www-data /var/www
RUN chown www-data:www-data /www
RUN chown -R www-data:www-data /cache
RUN chown -R www-data:www-data /symfony

COPY conf/sudoers.d/99_www-data /etc/sudoers.d/
RUN curl -L -s https://github.com/maxcnunes/waitforit/releases/download/v2.4.1/waitforit-linux_amd64 --output /usr/local/bin/waitforit && chmod a+x  /usr/local/bin/waitforit

USER www-data

# Download Core
RUN echo "Downloading Core package ${COREPACKAGE}"
RUN rm -rf app/Core &&  curl --header "PRIVATE-TOKEN: $TOKEN" https://git.sentinelo.com/api/v4/projects/514/packages/generic/${COREPACKAGE} | tar xvz --no-same-owner -C /app/ >> /dev/null

# Download View
RUN echo "Downloading View package ${VIEWPACKAGE}"
RUN rm -rf app/Core && curl --header "PRIVATE-TOKEN: $TOKEN" https://git.sentinelo.com/api/v4/projects/518/packages/generic/${VIEWPACKAGE} | tar xvz --no-same-owner -C /app/ >> /dev/null

## Download Admin
#RUN echo "Downloading Admin realease ${ADMINTAG}"
#RUN rm -rf app/Core && curl -s https://download.cockpitlabs.io/Admin-${ADMINTAG}.tar.gz | tar xvz --no-same-owner -C /app/ >> /dev/null

COPY conf/core.env /app/Core/.env


ENTRYPOINT "/start.sh"
