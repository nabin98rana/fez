<?php

// Usage:
//   sudo -u fez-user php setup-users.php
//   
// Note: this is a subset of what setup.php runs.
// It just creates users and groups if they're not there.
// setup.php does this and then assigns groups to roles
// which should trigger an auth index.

require_once(__DIR__ . '/../setuplib.php');
setup_users($verbose=true);
//setup_roles($solrindex=true, $verbose=true);

