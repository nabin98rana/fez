FROM uqlibrary/docker-nginx:6

COPY etc/nginx/conf.d/ /etc/nginx/conf.d/
COPY etc/nginx/rules/ /etc/nginx/rules/

RUN mkdir -p /espace/data && \
  mkdir -p /espace_san/incoming && \
  sed -i "s/keepalive_timeout 5 5;/keepalive_timeout 10 10;/" /etc/nginx/nginx.conf && \
  sed -i "s/send_timeout 10;/send_timeout 60;/" /etc/nginx/nginx.conf && \
  sed -i "s/client_body_timeout 10;/client_body_timeout 60;/" /etc/nginx/nginx.conf && \
  usermod -u 1000 nobody
