FROM php:7.2-cli

RUN apt-get update \
	&& apt-get install -y apt-utils

RUN apt-get install -y libbz2-dev \
	&& docker-php-ext-install -j$(nproc) bz2 \
	&& apt-get install -y libicu-dev \
	&& docker-php-ext-install -j$(nproc) intl