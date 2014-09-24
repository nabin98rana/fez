IMPORTANT
------------------------------------------------------------
DO NOT RUN THIS ON PRODUCTION

WHAT
------------------------------------------------------------

Testing suite for webservice api for Fez Digital Object Repository
systems.

Work sponsored by Charles Darwin University.

Authors:
* Matt Sammarco matts@catalyst-au.net
* Daniel Bush danb@catalyst-au.net

REQUIREMENTS
------------------------------------------------------------
* Httpful https://github.com/nategood/httpful
  * installed in this directory
* behat
  * we use the one included with fez

HOW IT WORKS
------------------------------------------------------------
* The idea is to take an existing fez based on recent upstream
  version with the api code (and these tests) and run these tests.
* The tests use httpful to hit your fez site
* You'll need to configure conf.ini to point to your development or
  testing fez url and identify relevant records with different
  attributes set, that the system can request and test against
* Each scenario (there are >=1 per .feature file) will
  instantiate the FeatureContext class in
  feature/bootstrap/FeatureContext.php .
  All the testing logic is in that class.
* Please note you may need to change some values within these feature files
  to align with your fez instance. For example, in some cases the display
  type you're using to test may not have the fields specified in the behat
  tests.

TODO:

Ideally, we should be using a blank fez installation with fixtures
to represent collections, records and communities etc.

However given time and project constraints, the current set up relies
on an existing fez installation.

To test the api via the browser it is recommended to use a REST client
such as 'Postman' for chrome. Alternatively, you can force the api to
use your credentials over the basic auth credentials by setting
APP_API_USERNAME and APP_API_PASSWORD in init.php.


SETTING UP FOR TESTING
------------------------------------------------------------

There should a be a

  features/

subdirectory .

A conf.ini file will need to be created and configured
to provide relevant information for accessing a particular fez
installation and the records therein.

1) CREATE conf.ini
Create a

  features/conf.ini

file.

Use the dist file:

  cp features/conf-dist.ini features/conf.ini

and then configure it for your particular fez installation.

2) create or select display types as required by conf.ini

3) create records/collections/communities as required conf.ini
   It is recommended you CREATE NEW COLLECTIONS containing only a few
   test records as required by conf.ini.

4) create users and groups and assign them to roles for the above
   communities as prescribed by conf.ini

   Run: sudo -u <fez-user> php tests/api/bin/setup.php

   This will attempt to create users, groups, associate users with
   groups and associate these groups with roles for public and
   restricted records in conf.ini.

   Note on SECURITY SETTINGS:
   ------------------------------------------------------------
   * a record can inherit from its parent OR
   * it can have settings:
     * if no viewer or lister settings, then the record can be publicly
       viewed or listed
     * all other settings (eg editor, creator, approver), if nothing
       is set, then the setting is *forbidden*

   For testing purposes:

   "restricted" = a collection or record that requires a specific
   fez group to be assigned to all relevant roles eg Lister, Viewer,
   Editor, Approver
   eg test_approver user belongs to test_approver group which is
   assigned the approver role for the record.

   "public" = a collection or record that has or inherits NO
   viewing/listing settings but otherwise has a specific fez group
   assigned to all relevant roles
   ------------------------------------------------------------

5) IMPORTANT: Ensure the following
   
     ini_set("display_errors", 0);

   The api code should set this at the end of init.php, but you may
   have overriden this in config.inc.php or some other mechanism.
   If php prints warnings or errors to the output buffer, this
   will break xml processing in the client.

* Recommend you turn 'html_errors' to 'off' in your php.ini to make
  stack traces more readable in the terminal.

* conf.ini: VERBOSE = 1 (in [general]) will increase output
  when xml is parsed; also use with "I should get xml" step

* you can add lines to print content to stdout or stderr for
  any of the behat tests in features/bootstrap/FeatureContext.php.

RUNNING TESTS
------------------------------------------------------------

  cd this-directory

  # To test behat is working, there is a hello world test:

  bin/run.sh --tags @helloworld  # runs the hello world scenario
  bin/run.sh features/00.helloworld.feature  # runs the whole file


  # Run everything:
  bin/run.sh

  # Pipe output to less:
  bin/run.sh |& less -r

  # To run one feature file:
  bin/run.sh features/somefeature.feature

  # Print with colour:
  bin/run.sh --ansi features/somefeature.feature

  # Tag your features and scenarios; you can use the same tag on different
  # things:

      Feature: foo

        @scenario1
        Scenario:
          ...

  # Then:
  bin/run.sh --tags @scenario1

  # To run one scenario in one feature file eg at line 4:
  bin/run.sh features/somefeature.feature:4

ERRORS and DEBUGGING

If you're getting errors, to increase verbosity
set VERBOSE = 1 in conf.ini.

Also, you can add --verbose to behat:
eg
  
  bin/run.sh --verbose features/somefeature.feature | less

This may show errors or warnings generated by SimpleXml when parsing xml etc.


WRITING TESTS
------------------------------------------------------------

1) Create a .feature file in features/
2) run: ../behat/bin/behat
   It should detect the feature file and suggest boiler plate
   for implementing the required step-code.
3) Put your step code in
   features/bootstrap/FeatureContext.php

   as instance methods for the FeatureContext class.

Note, the FeatureContext class will do things like
1) load the conf.ini file
2) maybe load yaml fixtures and related classes in fixtures/
3) use httpful to make http requests etc
3) is (re-)instantiated for EACH SCENARIO (within a feature file).

Features in features/ are numbered roughly in the order of complexity.
So 01 is for getting stuff. Then editing, creating, updating. Then
attachments.

The api assumes that the user is always using basic authentication.

By default, FeatureContext will set
  $this->username
  $this->password
to
  credentials.nogroups_username
  credentials.nogroups_password
in conf.ini .

You NEED to create these users, see the setup section.
