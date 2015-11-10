FROM uqlibrary/docker-fpm56:5

COPY etc/php-fpm.d/ /etc/php-fpm.d/
COPY etc/php.d/15-xdebug.ini /etc/php.d/15-xdebug.ini

RUN mkdir -p /espace/data && \
  mkdir -p /espace_san/incoming && \
  sed -i "s/memory_limit = 128M/memory_limit = 800M/" /etc/php.ini && \
  sed -i "s/post_max_size = 8M/post_max_size = 800M/" /etc/php.ini && \
  sed -i "s/upload_max_filesize = 30M/upload_max_filesize = 800M/" /etc/php.ini && \
  sed -i "s/; max_input_vars = 1000/max_input_vars = 5000/" /etc/php.ini
