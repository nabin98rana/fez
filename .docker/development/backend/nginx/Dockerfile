FROM uqlibrary/docker-nginx:6

RUN mkdir -p /espace/data && \
  mkdir -p /espace_san/incoming && \
  sed -i "s/keepalive_timeout 5 5;/keepalive_timeout 10 10;/" /etc/nginx/nginx.conf && \
  sed -i "s/send_timeout 10;/send_timeout 60;/" /etc/nginx/nginx.conf && \
  sed -i "s/client_body_timeout 10;/client_body_timeout 60;/" /etc/nginx/nginx.conf && \
  sed -i "s/fastcgi_param  REDIRECT_STATUS    200;/fastcgi_param  REDIRECT_STATUS    200;\nfastcgi_buffer_size 5M;\nfastcgi_buffers 256 512k;\n/" /etc/nginx/fastcgi.conf && \
  usermod -u 1000 nobody
