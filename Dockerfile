FROM uqlibrary/docker-fpm56:5

WORKDIR /var/app/current/
COPY . /var/app/current/

RUN chmod +x .docker/production/bootstrap.sh

VOLUME /var/app/current

ENTRYPOINT [".docker/production/bootstrap.sh"]
