FROM uqlibrary/docker-mysql-udf-preg:1

COPY etc/mysql/default.cnf /etc/mysql/conf.d/default.cnf

COPY bootstrap.sh /opt/bootstrap.sh

RUN chmod +x /opt/bootstrap.sh

ENTRYPOINT ["/opt/bootstrap.sh"]
