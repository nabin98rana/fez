# Fez

[ ![Codeship Status for uqlibrary/fez](https://codeship.com/projects/bb6396a0-7a03-0133-4c66-02b7238bd170/status?branch=master)](https://codeship.com/projects/118889)

<sub><sup>Developed with</sup></sub><br/>
[![alt text][2]][1]

  [1]: http://www.jetbrains.com/phpstorm/
  [2]: http://resources.jetbrains.com/assets/banners/jetbrains-com/phpstorm/phpstorm468x60_violet.gif (Smart IDE for PHP development with HTML, CSS &amp; JavaScript support)

Fez is a PHP / MySQL front end to the Fedora repository software. It is developed by the University of Queensland Library
as an open source project.

We will add more to github markdown wiki documentation soon, please see
[https://github.com/uqlibrary/fez/wiki](https://github.com/uqlibrary/fez/wiki "Wiki") for updates.

# Development quick start

Add the following to your hosts file:

    127.0.0.1    dev-fez.library.uq.edu.au fezdb

If you are using docker-machine, replace the 127.0.0.1 with the result of `docker-machine ip fez-vm`

Start the docker container using docker-compose:

    $ cd /path/to/repo/.docker/development
    $ docker-compose up -d

Once the containers are running proceed with the setup steps below.

## Setup

Install fez:

    $ cd /path/to/repo
    $ ./scripts/dev.sh

This will create a config.inc.php for you and setup some basic configs.

Once the script has completed you can now login:

[http://dev-fez.library.uq.edu.au:8080/login.php](http://dev-fez.library.uq.edu.au:8080/login.php)

    u: superadmin_test
    p: Ilovedonkey5

Run the sanity checks:

[http://dev-fez.library.uq.edu.au:8080/upgrade/check_sanity.php](http://dev-fez.library.uq.edu.au:8080/upgrade/check_sanity.php)

Celebrate your new Fez dev site!

## Testing

To run the tests:

    $ cd /path/to/repo
    $ ./scripts/run-tests.sh
    
To visually see the functional tests running in a browser, VNC into dev-fez.library.uq.edu.au:5900 with
the password `secret`.
    
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
