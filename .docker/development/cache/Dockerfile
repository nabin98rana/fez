FROM uqlibrary/docker-base:3

RUN cd /var/cache/ && \
  mkdir -p file && \
  mkdir -p solr_upload && \
  mkdir -p templates_c && \
  mkdir -p xdebug

VOLUME /var/cache

ENTRYPOINT chmod -R 777 /var/cache; /usr/bin/tail -f /dev/null
