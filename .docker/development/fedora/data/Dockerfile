FROM uqlibrary/docker-base:3

RUN mkdir -p /data/objects && \
    mkdir /data/datastreams && \
    chmod -R 777 /data

VOLUME /data

ENTRYPOINT /usr/bin/tail -f /dev/null
