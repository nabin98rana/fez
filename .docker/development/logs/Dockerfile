FROM busybox:1.25.0

RUN mkdir /var/log/fez && cd /var/log/fez/ && \
  mkdir -p backend/app && \
  mkdir -p backend/fpm && \
  mkdir -p backend/nginx && \
  mkdir -p fedora_tomcat && \
  mkdir -p fedora && \
  mkdir -p solr

VOLUME /var/log/fez/

ENTRYPOINT chmod -R 777 /var/log/fez; /usr/bin/tail -f /dev/null
