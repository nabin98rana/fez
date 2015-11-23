FROM uqlibrary/docker-fpm56:5

WORKDIR /var/app/current/
COPY . /var/app/current/

RUN cd /var/cache/ && \
    mkdir file && \
    mkdir solr_upload && \
    mkdir templates_c && \
    mkdir xdebug && \
    mkdir tmp && \
    chown -R nobody /var/cache

RUN chmod +x .docker/production/bootstrap.sh

VOLUME /var/app/current
VOLUME /var/cache

ENTRYPOINT [".docker/production/bootstrap.sh"]
