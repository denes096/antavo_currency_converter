FROM php:7.4-apache

RUN mkdir -p /etc/apache2/ssl
RUN mkdir -p /var/www/html/backend/log/apache

ADD ./config/antavo_apache.conf /etc/apache2/sites-available/000-default.conf

ADD ./config/backend.antavo.io.crt /etc/apache2/ssl/
ADD ./config/backend.antavo.io.key /etc/apache2/ssl/

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN a2enmod ssl
RUN a2enmod headers
RUN a2enmod rewrite
RUN service apache2 restart