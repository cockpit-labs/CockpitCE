FROM php:7.2-apache-stretch
ARG CORETAG
ENV CORETAG ${CORETAG:-latest}
ARG VIEWTAG
ENV VIEWTAG ${VIEWTAG:-latest}
ENV COMPOSER_ALLOW_SUPERUSER=1

EXPOSE 80
WORKDIR /www

RUN echo "Using Core release $CORETAG"
RUN echo "Using View release $VIEWTAG"


RUN mkdir /app

ENV COMPOSER_ALLOW_SUPERUSER=1
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer --version

RUN apt-get update -qq \
    && apt-get install -qy \
        git \
        gnupg \
        iputils-ping \
        libicu-dev \
        libzip-dev \
        unzip \
        zip \
        jq \
        libpng-dev \
        mysql-client \
        zlib1g-dev  \
        wait-for-it \
        sudo \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*


# PHP Extensions
RUN docker-php-ext-configure zip --with-libzip && \
	docker-php-ext-install -j$(nproc) intl opcache pdo_mysql zip gd mbstring
RUN pecl install timecop-beta
COPY conf/php.ini /usr/local/etc/php/conf.d/app.ini

# Apache
COPY conf/cockpitce.conf /etc/apache2/sites-available/000-default.conf
COPY conf/apache.conf /etc/apache2/conf-available/app.conf

RUN a2enconf app
RUN a2enmod rewrite remoteip
RUN a2disconf other-vhosts-access-log
# DB stuff
COPY conf/start.sh /app

# Download Core
RUN echo "Downloading Core realease ${CORETAG}"
RUN rm -rf app/Core && curl -s https://download.cockpitlabs.io/Core-${CORETAG}.tar.gz | tar xvz -C /app/ >> /dev/nul

# Download View
RUN echo "Downloading View realease ${VIEWTAG}"
RUN rm -rf app/Core && curl -s https://download.cockpitlabs.io/View-${VIEWTAG}.tar.gz | tar xvz -C /app/ >> /dev/nul

#COPY app/Core /app/Core
#COPY app/view /app/view
#COPY app/lang /app/data/public/lang
COPY conf/core.env /app/Core/.env
RUN rm -rf /app/Core/var > /dev/null \
    && mkdir /app/Core/var \
    && mkdir -p /app/data/private \
    && mkdir -p /app/data/public \
    && mkdir -p /app/data/public/lang \
    && ln -s /app/Core/public /www/core \
    && ln -s /app/data/public /www/data \
    && ln -s /app/View/dist/lang /app/data/public/lang/view \
    && ln -s /app/View/dist /www/view

COPY conf/cockpitadmin-auth.json /app/data/public
COPY conf/cockpitview-auth.json /app/data/public

RUN chown -R www-data:www-data /app

ENTRYPOINT "/app/start.sh"



