FROM uqlibrary/docker-base:latest

RUN mkdir -p /var/lib/solr

VOLUME /var/lib/solr

ENTRYPOINT chmod -R 777 /var/lib/solr; /usr/bin/tail -f /dev/null
