FROM makuk66/docker-solr:4.10.4

COPY etc/schema.xml /opt/solr/example/solr/collection1/conf/schema.xml
COPY etc/solrconfig.xml /opt/solr/example/solr/collection1/conf/solrconfig.xml
# Increase the default jetty settings to allow for large query posts to Solr from Fez
RUN sed -i "s|<New class=\"org.eclipse.jetty.server.bio.SocketConnector\">|<New class=\"org.eclipse.jetty.server.bio.SocketConnector\"><Set name=\"requestHeaderSize\">1000000</Set><Set name=\"requestBufferSize\">1000000</Set>|" /opt/solr/example/etc/jetty.xml
RUN sed -i "s|<Configure id=\"Server\" class=\"org.eclipse.jetty.server.Server\">|<Configure id=\"Server\" class=\"org.eclipse.jetty.server.Server\"><Call name=\"setAttribute\"><Arg>org.eclipse.jetty.server.Request.maxFormContentSize</Arg><Arg>200000</Arg></Call>|" /opt/solr/example/etc/jetty.xml