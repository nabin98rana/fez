FROM busybox:1.25.0

RUN mkdir -p /var/cache && cd /var/cache/ && \
  mkdir file && \
  mkdir solr_upload && \
  mkdir templates_c && \
  mkdir xdebug && \
  mkdir tmp && \
  mkdir tmp/uploader && \
  mkdir dstree && \
  chmod -R 777 /var/cache

VOLUME /var/cache

ENTRYPOINT chmod -R 777 /var/cache; /usr/bin/tail -f /dev/null
