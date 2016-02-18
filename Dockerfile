FROM uqlibrary/docker-fpm56:7

WORKDIR /var/app/current/
COPY . /var/app/current/

RUN cd /var/cache/ && \
    mkdir file && \
    mkdir solr_upload && \
    mkdir templates_c && \
    mkdir xdebug && \
    mkdir tmp && \
    chown -R nobody /var/cache && \
    mkdir -p /var/app/current/public/include/htmlpurifier/library/HTMLPurifier/DefinitionCache/Serializer/HTML && \
    chmod -R 777 /var/app/current/public/include/htmlpurifier/library/HTMLPurifier/DefinitionCache/Serializer/HTML && \
    chown -R nobody /var/app/current/public/include/htmlpurifier/library/HTMLPurifier/DefinitionCache/Serializer/HTML

RUN chmod +x .docker/production/bootstrap.sh

VOLUME /var/app/current
VOLUME /var/cache

ENTRYPOINT [".docker/production/bootstrap.sh"]
