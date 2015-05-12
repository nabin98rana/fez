FROM makuk66/docker-solr:4.10.4

# For Solr 4.x
#COPY etc/schema.xml /opt/solr/example/solr/collection1/conf/schema.xml
#COPY etc/solrconfig.xml /opt/solr/example/solr/collection1/conf/solrconfig.xml
# Increase the default jetty settings to allow for large query posts to Solr from Fez
#RUN sed -i "s|<New class=\"org.eclipse.jetty.server.bio.SocketConnector\">|<New class=\"org.eclipse.jetty.server.bio.SocketConnector\"><Set name=\"requestHeaderSize\">1000000</Set><Set name=\"requestBufferSize\">1000000</Set>|" /opt/solr/example/etc/jetty.xml
#RUN sed -i "s|<Configure id=\"Server\" class=\"org.eclipse.jetty.server.Server\">|<Configure id=\"Server\" class=\"org.eclipse.jetty.server.Server\"><Call name=\"setAttribute\"><Arg>org.eclipse.jetty.server.Request.maxFormContentSize</Arg><Arg>200000</Arg></Call>|" /opt/solr/example/etc/jetty.xml


# For Solr 5.x - TODO: figure out how to create the base collection via a CURL api call once the container is up
# See https://cwiki.apache.org/confluence/display/solr/Collections+API
# and https://cwiki.apache.org/confluence/display/solr/Major+Changes+from+Solr+4+to+Solr+5
#COPY etc/schema.xml /opt/solr/server/solr/configsets/basic_configs/confschema.xml
#COPY etc/solrconfig.xml /opt/solr/server/solr/configsets/basic_configs/conf/solrconfig.xml
# Increase the default jetty settings to allow for large query posts to Solr from Fez
#RUN sed -i "s|<New class=\"org.eclipse.jetty.server.bio.SocketConnector\">|<New class=\"org.eclipse.jetty.server.bio.SocketConnector\"><Set name=\"requestHeaderSize\">1000000</Set><Set name=\"requestBufferSize\">1000000</Set>|" /opt/solr/server/etc/jetty.xml
#RUN sed -i "s|<Configure id=\"Server\" class=\"org.eclipse.jetty.server.Server\">|<Configure id=\"Server\" class=\"org.eclipse.jetty.server.Server\"><Call name=\"setAttribute\"><Arg>org.eclipse.jetty.server.Request.maxFormContentSize</Arg><Arg>200000</Arg></Call>|" /opt/solr/server/etc/jetty.xml