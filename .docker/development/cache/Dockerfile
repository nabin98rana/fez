FROM uqlibrary/docker-base:4

RUN cd /var/cache/ && \
  mkdir file && \
  mkdir solr_upload && \
  mkdir templates_c && \
  mkdir xdebug && \
  mkdir tmp

VOLUME /var/cache

ENTRYPOINT chmod -R 777 /var/cache; /usr/bin/tail -f /dev/null
