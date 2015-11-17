# Fez

<sub><sup>Developed with</sup></sub><br/>
[![alt text][2]][1]

  [1]: http://www.jetbrains.com/phpstorm/
  [2]: http://www.jetbrains.com/phpstorm/documentation/phpstorm_banners/phpstorm1/phpstorm468x60_violet.gif (Smart IDE for PHP development with HTML, CSS &amp; JavaScript support)
  
Fez is a PHP / MySQL front end to the Fedora repository software. It is developed by the University of Queensland Library 
as an open source project.

We will add more to github markdown wiki documentation soon, please see 
[https://github.com/uqlibrary/fez/wiki](https://github.com/uqlibrary/fez/wiki "Wiki") for updates.

# Development quick start

Add the following to your hosts file:

    127.0.0.1    dev-fez.library.uq.edu.au

Create the data directories:

    $ cd /path/to/repo/.docker/development && \
      mkdir -p data/mysql/fedoradb && \
      mkdir -p data/mysql/fezdb && \
      mkdir -p data/solr
      
Start the docker container using docker-compose:

    $ cd /path/to/repo/.docker/development
    $ docker-compose up -d

Once the containers are running proceed with the setup steps below.

## Setup

Install fez using the onscreen setup the credentials at 
[http://dev-fez.library.uq.edu.au:8080/setup/](http://dev-fez.library.uq.edu.au:8080/setup/)

This will create a config.inc.php for you and setup some basic configs. Next run the "Upgrade" once the setup completes. 
NB: When the upgrade finishes skip running the sanity check until the fez database has been seeded.

Next seed the fez database:

    $ cd /path/to/repo/.docker/development/backend/db/seed
    $ mysql -uroot -pdevelopment -h fezdb mysql < installdb.sql
    $ mysql -uroot -pdevelopment -h fezdb fez < cvs.sql
    $ mysql -uroot -pdevelopment -h fezdb fez < development.sql
    $ mysql -uroot -pdevelopment -h fezdb fez < workflows.sql
    $ mysql -uroot -pdevelopment -h fezdb fez < xsd.sql

Restart all the services:
 
$ docker-compose restart

Once all services have restarted, login at 
[http://dev-fez.library.uq.edu.au:8080/login.php](http://dev-fez.library.uq.edu.au:8080/login.php) with the superadmin 
credentials (u: superadmin_test p: Ilovedonkey5) and run the sanity check at: 
[http://dev-fez.library.uq.edu.au:8080/upgrade/check_sanity.php](http://dev-fez.library.uq.edu.au:8080/upgrade/check_sanity.php)
NB: You may see "Failed: Connect" for Fedora, this is currently expected and will be resolved with the datastore upgrade.

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
