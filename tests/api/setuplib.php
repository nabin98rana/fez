<?php

// Code here is responsible for setting things up before running
// tests, features or scenarios.
//
// It requires fez libraries to work.

require_once(__DIR__ . '/../../public/include/class.api.php');
require_once(APP_INC_PATH . "class.user.php");
require_once(APP_INC_PATH . "class.group.php");
require_once(APP_INC_PATH . "class.fedora_api.php");
require_once(APP_INC_PATH . "class.record.php");
require_once(APP_INC_PATH . "class.auth_index.php");

// Runs all the other setup programs.
//
// @param boolean $solrindex Whether to run solr index.
//
// Motivation for $solrindex is because we may want to run the
// database updates to build the users/groups as feature/scenario hook
// in behat but maybe not do solr.
// If you run solr, it may start indexing a whole lot of stuff,
// and you'll need to wait for it to finish.

function setup($ignoreroles=false, $solrindex=true, $verbose=false)
{
    check_records($verbose);
    setup_users($verbose);
    if (!$ignoreroles) {
        setup_roles($solrindex, $verbose);
    }
}

// Get and parse conf.ini and conf-dist.ini.
//
// @return array of parsed conf.ini and conf-dist.ini

function get_conf()
{
    $confdistpath = __DIR__ . '/conf-dist.ini';
    $confpath = __DIR__ . '/conf.ini';

    if (!file_exists($confdistpath)) {
        throw new Exception("Can't find $confdistpath");
    }
    if (!file_exists($confpath)) {
        throw new Exception("Can't find $confpath");
    }
    $dist = parse_ini_file($confdistpath, true);
    $conf = parse_ini_file($confpath, true);


    // Load credentials:

    $users = array();
    $passwords = array();
    $groups = array();

    foreach ($conf['credentials'] as $k => $v) {
        list($role, $type) = explode('_', $k);
        switch ($type) {
            case 'username': 
                $users[$role] = $v;
                break;
            case 'password': 
                $passwords[$role] = $v;
                break;
            case 'group': 
                $groups[$role] = $v;
                break;
            default:
                echo "WARN: conf.ini: credentials.$k not recognised" . PHP_EOL;
                break;
        }
    }

    return array($conf, $dist, $users, $passwords, $groups);
}

// Run this before other functions to do basic sanity checks.
//
// Runs get_conf which will blow up if bad. 

function check_records($verbose = false)
{
    list($conf, $dist, $users, $passwords, $groups) = get_conf();
    $memberof = function($pid, $colpid) {
        $arr = Record::getParents($pid);
        return in_array($colpid, $arr);
    };

    // Add more checks here...
    if (!isset($conf['restricted_community']['collection'])) {
        throw new Exception("restricted_community.collection not set in your conf.ini.");
    }
    if (!isset($conf['restricted_community']['record'])) {
        throw new Exception("restricted_community.record not set in your conf.ini.");
    }
    if (!isset($conf['public_community']['collection'])) {
        throw new Exception("public_community.collection not set in your conf.ini.");
    }
    if (!isset($conf['public_community']['record'])) {
        throw new Exception("public_community.record not set in your conf.ini.");
    }
    if (!isset($conf['public_community']['unpublished_record'])) {
        throw new Exception("public_community.record not set in your conf.ini.");
    }
    if (!isset($conf['restricted_community']['record_with_multiple_collections'])) {
        throw new Exception("restricted_community.record_with_multiple_collections not set in your conf.ini.");
    }
    if (!isset($conf['edit_security']['record'])) {
        throw new Exception("edit_security.record not set in your conf.ini.");
    }

    $ok = $memberof(
        $r = $conf['public_community']['record'],
        $c = $conf['public_community']['collection']
    );
    if (!$ok) {
        throw new Exception("public community: record $r not in $c");
    }
    $ok = $memberof(
        $r = $conf['restricted_community']['record'],
        $c = $conf['restricted_community']['collection']
    );
    if (!$ok) {
        throw new Exception("restricted community: record $r not in $c");
    }
    $ok = $memberof(
        $r = $conf['public_community']['unpublished_record'],
        $c = $conf['public_community']['collection']
    );
    if (!$ok) {
        throw new Exception("public community: unpublished_record $r not in $c");
    }

    // Make the unpublished record unpublished...
    $pid = $conf['public_community']['unpublished_record'];
    $rec = new RecordObject($pid);
    $rec->setStatusId(4);


}


// Create test users and groups in fez.

function setup_users($verbose = false)
{

    list($conf, $dist, $users, $passwords, $groups) = get_conf();

    // Do we have ALL credentials set in conf.ini?

    foreach ($dist['credentials'] as $k => $v) {
        if (!isset($conf['credentials'][$k])) {
            echo "conf.ini: credentials.$k NOT SET!" . PHP_EOL;
            die();
        }
    }

    // Make groups...

    foreach ($groups as $role => $groupname) {
        if (Group::getID($groupname)) {
            //echo "FOUND test group $groupname" . PHP_EOL;
        } else {
            if ($verbose) echo "CREATING test group $groupname" . PHP_EOL;
            $_POST['users'] = null;
            $_POST['title'] = $groupname;
            $_POST['status'] = 'active';
            $res = Group::insert(); //var_export($res);
        }
    }

    // Make some users...

    foreach ($users as $role => $username) {
        $password = $passwords[$role];
        switch ($role) {
            case 'admin':
                $_POST['administrator'] = true;
                $_POST['super_administrator'] = false;
                break;
            case 'superadmin':
                $_POST['administrator'] = true;
                $_POST['super_administrator'] = true;
                break;
            default:
                $_POST['administrator'] = false;
                $_POST['super_administrator'] = false;
                break;
        }
        $_POST['ldap_authentication'] = false;
        $_POST['username'] = $username;
        $_POST['password'] = $password;
        $_POST['email'] = $username . "@example.com";
        $_POST['full_name'] = "$role $username";
        $_POST['family_name'] = "$username";
        $_POST['change_password'] = true;
        if ($id = User::getUserIDByUsername($username)) {
            //echo "id is $id" . PHP_EOL;
            $_POST['id'] = $id;
            if ($verbose) echo "Updating user $role/$username" . PHP_EOL;
            $res = User::update($_POST['super_administrator']);
        } else {
            if ($verbose) echo "CREATING user $username ($role)" . PHP_EOL;
            $res = User::insert(); //var_export($res);
        }
    }

    // Associate users with groups...

    foreach ($users as $role => $username) {
        if (!isset($groups[$role])) {
            continue;
        }
        $groupname = $groups[$role];
        $uid = User::getUserIDByUsername($username);
        $gid = Group::getID($groupname);
        $list = Group::getUserAssocList($gid);
        if (array_key_exists($uid, $list)) {
            //echo "Associating $username with group $groupname... " ;
            //echo "ALREADY DONE" . PHP_EOL;
        } else {
            if ($verbose) echo "Associating $username with group $groupname... " ;
            $res = Group::associateUser($gid, $uid);
            if ($res) {
                if ($verbose) echo "DONE" . PHP_EOL;
            } else {
                if ($verbose) echo "NOT DONE" . PHP_EOL;
            }
        }
    }

    return array($users, $passwords, $groups);
}

// Set up security permissions on existing test pids in conf.ini.
// 
// Once we have fez groups, we can assign them to specific roles for a
// given record or collection.
//
// Based on checkQuickAuthFezACML (class.record.php) .

function setup_roles($solrindex = true, $verbose = false)
{
    list($conf, $dist, $users, $passwords, $groups) = get_conf();

    // Build role arrays...

    $roles = array();
    $public_roles = array();

    foreach ($groups as $role => $groupname) {
        $gid = Group::getID($groupname);
        $role = strtolower($role);
        $roles[$role] = $gid;

        // Set public_roles:
        switch ($role) {
            case 'viewer':
            case 'lister':
                // These roles will default to unrestricted access if
                // we don't set anything (and inherit is off).
                break;
            default:
                $public_roles[$role] = $gid;
                break;
        }
    }

    // Solr indexing is recursive by default and will take time if
    // we do this on a community or collection.
    // So we'll index the records.

    if ($verbose) echo 'Setting up public record roles' . PHP_EOL;
    $pid = $conf['public_community']['record'];
    assign_roles_to_pid($pid, array(), true, $solrindex, $verbose);

    if ($verbose) echo 'Setting up unpublished record roles' . PHP_EOL;
    $pid = $conf['public_community']['unpublished_record'];
    assign_roles_to_pid($pid, array(), true, $solrindex, $verbose);

    if ($verbose) echo 'Setting up restricted record roles' . PHP_EOL;
    $pid = $conf['restricted_community']['record'];
    assign_roles_to_pid($pid, $roles, false, $solrindex, $verbose);

    if ($verbose) echo 'Setting up restricted record roles' . PHP_EOL;
    $pid = $conf['restricted_community']['record_with_multiple_collections'];
    assign_roles_to_pid($pid, $roles, false, $solrindex, $verbose);

    if ($verbose) echo 'Setting up restricted record roles' . PHP_EOL;
    $pid = $conf['edit_security']['record'];
    assign_roles_to_pid($pid, $roles, false, $solrindex, $verbose);

    if (isset($conf['laal']['record'])) {
        if ($verbose) echo 'Setting up restricted record roles' . PHP_EOL;
        $pid = $conf['laal']['record'];
        assign_roles_to_pid($pid, $roles, false, $solrindex, $verbose);
    }


}

// Set roles and permissions on a pid.
//
// @param array $roles same format as make_fezacml_template

function assign_roles_to_pid($pid, array $roles, $inherit = false, $solrindex = true, $verbose = false)
{
    $xml = make_fezacml_template($roles, $inherit);
    if ($verbose) echo "- $pid updating fezacml in fedora" . PHP_EOL;
    Fedora_API::callModifyDatastreamByValue(
        $pid, 'FezACML',
        'A', 'Fez Access Control Markup Language',
        $xml, 'text/xml', true
    );
    if ($solrindex) {
        if ($verbose) echo "- $pid indexing in solr" . PHP_EOL;
        AuthIndex::setIndexAuth($pid, true);
    }
}

function make_tag($tagname, $content='')
{
    return "<$tagname>$content</$tagname>" . "\n";
}

// Modelled on what current fezacml datastreams look like...
//
// This is purely to generate the expected fezacml group->role
// permissions we will be testing for in the API.
//
// @param array $groups should map role => gid; role should be lowercase

function make_fezacml_template(array $roles, $inherit_security=false)
{
    $inherit_security = ($inherit_security ? 'on' : 'off');

    // These are the roles that are in fez.
    $acml_names = array(
        "Creator", "Editor", "Approver", "Viewer", "Lister",
        "Commentor", "Comment_Viewer", "Annotator"
    );

    $start =  '<FezACML xmlns:xsi="http://www.w3.org/2001/XMLSchema">' . "\n";
    $start .= '<rule>' . "\n";
    $body = array();

    foreach ($acml_names as $name) {

        if (isset($roles[strtolower($name)])) {
            $gids = array($roles[strtolower($name)]);
        } else {
            // Create empty Fez_Group - which means "don't restrict to
            // fez group".
            $gids = array('');
        }

        $xml = '';
        $xml .= "<role name=\"{$name}\" >" . "\n";
        $xml .= make_tag('AD_Group');
        $xml .= make_tag('AD_User');
        $xml .= make_tag('AD_DistinguishedName');

        // in_Fez = "if fez authenticated you, you're ok"
        // Turn off, and set Fez_Group only.

        $xml .= make_tag('in_Fez', 'off');

        foreach ($gids as $gid) {
            $xml .= make_tag('Fez_Group', $gid);
        }

        $tagnames = array(
            'eduPersonTargetedID',
            'eduPersonAffiliation',
            'eduPersonScopedAffiliation',
            'eduPersonPrimaryAffiliation',
            'eduPersonPrincipalName',
            'eduPersonOrgUnitDN',
            'eduPersonPrimaryOrgUnitDN',
        );

        $xml .= implode('', array_map(
            function($tagname) {
                return make_tag($tagname);
            }, $tagnames));

        $xml .= "</role>" . "\n";
        $body[] = $xml;
    }

    $body = implode('', $body);


    $end  = '</rule>' . "\n";
    $end .= "<inherit_security>{$inherit_security}</inherit_security>" . "\n";

    $end .= '</FezACML>' . "\n";

    return $start . $body . $end;
}

