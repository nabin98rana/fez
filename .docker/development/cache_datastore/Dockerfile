FROM uqlibrary/docker-base:latest

RUN cd /var/local/ && \
  mkdir -p logs/backend/fpm &&
  mkdir -p logs/backend/nginx && 
  mkdir -p logs/fedora_tomcat && 
  mkdir -p logs/fedora && 
  mkdir -p logs/fez && 
  mkdir -p logs/solr

VOLUME /var/local/

CMD ["/bin/echo", "Logs datastore created"]
