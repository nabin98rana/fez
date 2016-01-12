FROM uqlibrary/docker-fedoracommons:4

COPY etc/tomcat-users.xml /opt/fedora/tomcat/conf/tomcat-users.xml
COPY etc/fedora.fcfg /opt/fedora/server/config/fedora.fcfg
COPY etc/fedora-users.xml /opt/fedora/server/config/fedora-users.xml

RUN mkdir /data