FROM uqlibrary/docker-nginx-fez:latest

COPY etc/nginx/conf.d /etc/nginx/conf.d
COPY etc/nginx/espace_rewrite_rules.conf /etc/nginx/espace_rewrite_rules.conf
COPY etc/php-fpm.d/ /etc/php-fpm.d/
COPY etc/15-xdebug.ini /etc/php.d/15-xdebug.ini

RUN mkdir -p /espace/data && \
  mkdir -p /espace_san/incoming && \
  rm -f /etc/php.d/20-mssql.ini && \
  rm -f /etc/php.d/30-pdo_dblib.ini && \
  sed -i "s/memory_limit = 128M/memory_limit = 800M/" /etc/php.ini && \
  sed -i "s/post_max_size = 8M/post_max_size = 800M/" /etc/php.ini && \
  sed -i "s/upload_max_filesize = 30M/upload_max_filesize = 800M/" /etc/php.ini && \
  sed -i "s/keepalive_timeout 5 5;/keepalive_timeout 10 10;/" /etc/nginx/nginx.conf && \
  sed -i "s/send_timeout 10;/send_timeout 60;/" /etc/nginx/nginx.conf && \
  sed -i "s/client_body_timeout 10;/client_body_timeout 60;/" /etc/nginx/nginx.conf && \
  sed -i "s/; max_input_vars = 1000/max_input_vars = 5000/" /etc/php.ini && \
  usermod -u 1000 nobody
