FROM uqlibrary/docker-mysql-udf-preg:1

COPY etc/mysql/default.cnf /etc/mysql/conf.d/default.cnf

COPY tmpfs.sh /tmpfs.sh
RUN chmod +x /tmpfs.sh

RUN chmod 755 /etc/mysql/conf.d/default.cnf

ENTRYPOINT ["/tmpfs.sh"]

CMD ["mysqld", "--datadir=/opt/tmpfs", "--user=mysql"]
