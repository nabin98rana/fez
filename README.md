#Fez

Fez is a PHP / MySQL front end to the Fedora repository software. It is developed by the University of Queensland Library 
as an open source project.

We will add more to github markdown wiki documentation soon, please see 
[https://github.com/uqlibrary/fez/wiki](https://github.com/uqlibrary/fez/wiki "Wiki") for updates.

# Development quick start

Add the following to your hosts file:

    127.0.0.1    dev-fez.library.uq.edu.au

Create the data directories:

    $ sudo mkdir -p /data/docker/fez/fedora && \
      sudo mkdir -p /data/docker/fez/espace_san/incoming && \
      sudo chown -R 999:999 /data/docker/fez

Mount the eSpace staging Fedora datastore (xml, pdfs etc) and production SAN into your docker host by adding 
the following to your /etc/fstab changing the uid and gid to the user/group you run docker with: 
    
    //libzoo.library.uq.edu.au/espacestage/data   /data/docker/fez/fedora       cifs   noauto,credentials=/root/libzoo.credentials,uid=1000,gid=1000,rw          0 0
    //lib-staff.library.uq.edu.au/espace          /data/docker/fez/espace_san   cifs   auto,credentials=/root/espace.credentials,workgroup=uq,uid=48,gid=10,ro   0 0

Create the credentials file used in the mount command above:

    $ vi /root/libzoo.credentials
    $ vi /root/espace.credentials

The files above should contain the username/password on a separate line. For libzoo.credentials:

    domain=libzoo
    username=user
    password=pass

And for espace.credentials:

    domain=UQ
    username=user
    password=pass

NB: The real credentials are stored in corporate vault.

Now mount them:

    $ sudo mount /data/docker/fez/fedora
    $ sudo mount /data/docker/fez/espace_san

NB: This datastore gets refreshed (rsync/hard) each day so it’s safe enough to be a little destructive.

Start the docker container using docker-compose:

    $ cd /var/shared/.docker/development
    $ docker-compose up -d

Once the container is running open the following URL to test the container is working

    $ curl -v http://dev-fez.library.uq.edu.au:8080/api/ping

If all is well, you should see the response "pong".

## Setup

Install fez using the onscreen setup the credentials at 
[http://dev-fez.library.uq.edu.au:8080/setup/](http://dev-fez.library.uq.edu.au:8080/setup/)

This will create a config.inc.php for you and setup some basic configs, but you want to override all that 
in the next mysql imports.

You can run the "Upgrade" after this but it currently errors. That’s logged as a bug to be fixed ASAP but the mysql 
commands in this setup next will negate that for us UQ developers, but only if we manually import fez_config from 
e.g. espace_staging and then run the dev.fez.config.sql over the top of it.

Import the fez and fedora data into the two database servers (one for Fez, the other for Fedora). 
The Fez one takes about 10 mins to load. The Fedora is less than a minute. The first command installs the preg 
functions (can’t get this into mysql-first-time.sql until docker update the official mysql container with 
initialisation SQL commands like they have with postgresql). The second strips the definers from the create 
view statements (this will be moved somewhere else like the dump file later).

    $ wget -O installdb.sql https://raw.githubusercontent.com/mysqludf/lib_mysqludf_preg/testing/installdb.sql && \
    $ mysql -uroot -pdevelopment -h fezdb mysql < installdb.sql && \
    $ sed -i 's/DEFINER=[^*]*\*/\*/g' fez.sql && \
    $ mysql -uroot -pdevelopment -h fezdb fez < fez.sql && \
    $ mysql -uroot -pdevelopment -h fezdb fez < dev.fez.config.sql && \
    $ mysql -uroot -pdevelopment -h fedoradb -P 3307 fedora3 < fedora3.sql

Load up the fez site in your browser and reindex Solr, starting with the communities and collections.

Run this to index the communities and collections first (most important):

    insert into fez_fulltext_queue (ftq_pid, ftq_op) SELECT rek_pid, 'I' from fez_record_search_key  WHERE rek_object_type = 1 OR rek_object_type = 2;
    
Go to [http://dev-fez.library.uq.edu.au:8080/misc/process_fulltext_queue.php](http://dev-fez.library.uq.edu.au:8080/misc/process_fulltext_queue.php) to trigger a process.

You can watch it as it completes in the background process monitor gui at: [http://dev-fez.library.uq.edu.au:8080/my_processes.php](http://dev-fez.library.uq.edu.au:8080/my_processes.php)

Once that has completed run this to index all the other content:

    insert into fez_fulltext_queue (ftq_pid, ftq_op) SELECT rek_pid, 'I' from fez_record_search_key  WHERE rek_object_type = 3;

Go to [http://dev-fez.library.uq.edu.au:8080/misc/process_fulltext_queue.php](http://dev-fez.library.uq.edu.au:8080/misc/process_fulltext_queue.php) to trigger a process

Celebrate your new Fez dev site!

## Useful Links

### Fedora Tomcat Admin

[http://dev-fez.library.uq.edu.au:10081/manager/html](http://dev-fez.library.uq.edu.au:10081/manager/html)

u: fedoraAdmin
p: fedoraAdmin

NB: Sometimes fedora (inside tomcat, inside the fedora container) doesnt start on boot because maybe the fedora sql 
container wasn't ready in time. If this happens just go to the above link and login and click start on the fedora tomcat 
application.

### Solr Dashboard

[http://dev-fez.library.uq.edu.au:8983/solr](http://dev-fez.library.uq.edu.au:8983/solr)
