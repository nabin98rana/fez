#!/bin/bash
mysql -uroot -pdevelopment -h fezdb mysql < installdb.sql && \
mysql -uroot -pdevelopment -h fezdb fez < citation.sql && \
mysql -uroot -pdevelopment -h fezdb fez < cvs.sql && \
mysql -uroot -pdevelopment -h fezdb fez < development.sql && \
mysql -uroot -pdevelopment -h fezdb fez < workflows.sql && \
mysql -uroot -pdevelopment -h fezdb fez < xsd.sql