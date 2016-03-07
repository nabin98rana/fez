FROM uqlibrary/docker-base:5

RUN cd /var/cache/ && \
  mkdir file && \
  mkdir solr_upload && \
  mkdir templates_c && \
  mkdir xdebug && \
  mkdir tmp && \
  mkdir dstree

VOLUME /var/cache

ENTRYPOINT chmod -R 777 /var/cache; /usr/bin/tail -f /dev/null
