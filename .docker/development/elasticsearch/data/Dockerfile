FROM busybox:1.25.0

RUN mkdir -p /usr/share/elasticsearch/data

VOLUME /usr/share/elasticsearch/data

ENTRYPOINT chmod -R 777 /usr/share/elasticsearch/data; /usr/bin/tail -f /dev/null