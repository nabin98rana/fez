<?php

// Usage:
//   sudo -u fez-user php setup.php
//   
// Note:
// $ignoreroles = false => we assign roles to pids in conf.ini (not
// just setup users and groups).
// $solrindex = true => and we additionally do solr index auth after
// setting up the roles.

require_once(__DIR__ . '/../setuplib.php');
setup($ignoreroles=false, $solrindex=true, $verbose=true);
