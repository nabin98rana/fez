<?php
// +----------------------------------------------------------------------+
// | LDAP to Fez author import and synchronisation utility                |
// +----------------------------------------------------------------------+
// | Copyright (c) 2007 The University of Queensland.                     |
// |                                                                      |
// | This tool connects to a dedicated Oracle feed of Aurion data, and    |
// | imports it into Fez. If the author already exists in Fez, the        |
// | existing details are left alone. Otherwise, a new author is created. |
// +----------------------------------------------------------------------+
// | Author: Lachlan Kuhn <l.kuhn@library.uq.edu.au>                      |
// | Date:   04/05/2007                                                   |
// +----------------------------------------------------------------------+
// | Note: At the bottom of this file you will find the SQL for creating  |
// | the Oracle view. Get someone at ITS to re-create this view if it     |
// | goes missing in action.                                              |
// +----------------------------------------------------------------------+

////////////////////////////////////////////////////////////////////////////
// Production config variables
////////////////////////////////////////////////////////////////////////////

@DEFINE("FEZ_HOST",           "");
@DEFINE("FEZ_DB",             "");
@DEFINE("FEZ_TABLE_PREFIX",   "");
@DEFINE("FEZ_USER",           "");
@DEFINE("FEZ_PASSWD",         "");

@DEFINE("ORACLE_TNS_NAME",    "");
@DEFINE("ORACLE_USER",        "");
@DEFINE("ORACLE_PASSWD",      "");


$ldap2fez = new LDAP2FEZ();
$ldap2fez->gameOn();

class LDAP2FEZ {

    /**
     * oracleTime
     *
     * This method connects to the Oracle Aurion view, and pulls in the records we're interested in. For each record,
     * we attempt to add it to the Fez table - if the right conditions are met. This is all pretty straight-forward
     * for now, though we may wish to beef this up in the future and record more information.
     *
     */
    function oracleTime() {
        putenv("ORACLE_HOME=/usr/lib/oracle/11.2/client64");                // For great justice
        putenv("TNS_ADMIN=/usr/lib/oracle/11.2/client64/network/admin");    // For even greater justice

        // Establish DB connection
        $oracleConn = oci_connect(ORACLE_USER, ORACLE_PASSWD, ORACLE_TNS_NAME);
        if (!$oracleConn) {
            $e = oci_error();
            die("Error: Could not connect to Oracle database.\n{$e['message']}\n");     // ABORT, ABORT, ABORT!
        }

        // Set up query
        $query = "
            SELECT * FROM (
            SELECT T000F005_WAMI_KEY, T000F145_SURNAME_FOR_LETTERS, T000F020_GIVEN_NAMES, T000F025_PREFERRED_NAME, T000F030_SALUTATION, T000F160_EMAIL_ADDRESS, T001F070_ACTUAL_ORG_UNIT_NO, T001F120_ACTUAL_JOB_CODE, T900F015_SHORT_DESCRIPTION, KERBEROS
            FROM OPS\$LDAP.LIBRARY_STAFF_VIEW
            WHERE
               (  T001F120_ACTUAL_JOB_CODE = 'ACAD'
               OR T001F120_ACTUAL_JOB_CODE = 'ADJNCT'
               OR T001F120_ACTUAL_JOB_CODE = 'AFFIL'
               OR T001F120_ACTUAL_JOB_CODE = 'CONJNT'
               OR T001F120_ACTUAL_JOB_CODE = 'HLTPRO'
               OR T001F120_ACTUAL_JOB_CODE = 'HONOR'
               OR T001F120_ACTUAL_JOB_CODE = 'INDFEL'
               OR T001F120_ACTUAL_JOB_CODE = 'LIB'
               OR T001F120_ACTUAL_JOB_CODE = 'PROFN'
               OR T001F120_ACTUAL_JOB_CODE = 'RESA'
               OR T001F120_ACTUAL_JOB_CODE = 'RESG'
               OR T001F120_ACTUAL_JOB_CODE = 'RESTCH'
               OR T001F120_ACTUAL_JOB_CODE = 'SCHOL'
               OR T001F120_ACTUAL_JOB_CODE = 'SCI'
               OR T001F120_ACTUAL_JOB_CODE = 'SENADM'
               OR T001F120_ACTUAL_JOB_CODE = 'UQPTNR'
               OR T001F120_ACTUAL_JOB_CODE = 'VISIT'
               )
            AND KERBEROS IS NOT NULL
            ORDER BY T000f145_SURNAME_FOR_LETTERS, T000F025_PREFERRED_NAME
            )
        ";

        $oracleParse = oci_parse($oracleConn, $query);
        if (!$oracleParse) {
            die("Error: Could not parse the requested author-fetch query. Please investigate.\n");
        }

        // Execute
        $oracleExec = oci_execute($oracleParse);
        if (!$oracleExec) {
            die("Error: Could not execute the requested author-fetch query. Please investigate.\n");
        }

        // Let's roll
        while ($row = oci_fetch_row($oracleParse)) {
            /*
            0   T000F005_WAMI_KEY
            1   T000F145_SURNAME_FOR_LETTERS
            2   T000F020_GIVEN_NAMES
            3   T000F025_PREFERRED_NAME
            4   T000F030_SALUTATION
            5   T000F160_EMAIL_ADDRESS
            6   T001F070_ACTUAL_ORG_UNIT_NO
            7   T001F120_ACTUAL_JOB_CODE
            8   T900F015_SHORT_DESCRIPTION
            9   KERBEROS
            */
            // ($org_username, $org_staff_id, $display_name, $fname, $lname, $title)
            // This could really do with some objectisation, but ... it's only a one-off, right?
            echo "Processing " . trim(strtoupper($row[1])) . ", " . trim($row[3]) . "... ";
            LDAP2FEZ::addOrUpdateAuthor(trim($row[9]), trim($row[0]), trim($row[3]) . ' ' . trim($row[1]), trim($row[3]), trim($row[1]), trim($row[4]));
        }

        // Our work here is done.
        oci_close($oracleConn);

        return;

    }

    /**
     * printRecordsetRow
     *
     * This is a test method intended purely for observing the results of a returned recordset.
     * This would probably be better implemented as an array param, for scalability, but I JUST DON'T CARE.
     *
     * Params are as follows:
     * T000F005_WAMI_KEY, T000F145_SURNAME_FOR_LETTERS, T000F020_GIVEN_NAMES, T000F025_PREFERRED_NAME, T000F030_SALUTATION, T000F160_EMAIL_ADDRESS, T001F070_ACTUAL_ORG_UNIT_NO, T001F120_ACTUAL_JOB_CODE, T900F015_SHORT_DESCRIPTION
     */
    function printRecordsetRow($row1, $row2, $row3, $row4, $row5, $row6, $row7, $row8, $row9) {
        echo "\n" . $row1 . "\n\t" . $row2 . "\n\t" . $row3 . "\n\t" . $row4 . "\n\t" . $row5 . "\n\t" . $row6 . "\n\t" . $row7 . "\n\t" . $row8 . "\n\t" . $row9;
        return;
    }

    /**
     * addOrUpdateAuthor
     *
     * Method used to write the author to the Fez authors table, if the author doesn't already appear to be
     * there. If the author does exist, we'll leave the record entirely intact - the assumption here is that
     * once an author has been imported into Fez, the Fez record becomes authoritative - that is, we wish to
     * preserve any local alterations to the author's details. The 'updated' datstamp is updated, but no
     * other details are changed.
     *
     * Future: We may wish to extend this function to also record an author's organisational unit
     * affiliations, but this has been flagged as "non-essential" for now.
     */
    function addOrUpdateAuthor($org_username, $org_staff_id, $display_name, $fname, $lname, $title) {

        global $fezConn;                    // We want to reference this global variable from the main method.

        // Populate SQL vars
        $org_username = mysql_escape_string($org_username);
        $org_staff_id = mysql_escape_string($org_staff_id);
        $display_name = mysql_escape_string($display_name);
        $fname = mysql_escape_string($fname);
        $lname = mysql_escape_string($lname);
        $title = mysql_escape_string($title);

        // Set up the query
        $query = "
        INSERT INTO " . FEZ_TABLE_PREFIX . "_author
        (aut_org_username, aut_org_staff_id, aut_display_name, aut_fname, aut_lname, aut_title,  aut_created_date)
        VALUES
        ('$org_username', '$org_staff_id', '$display_name', '$fname', '$lname', '$title', now())
        ON DUPLICATE KEY UPDATE aut_update_date = now();
        ";

        // Run the query
        $queryResult = mysql_query($query, $fezConn);
        if (!$queryResult) {
            echo "QUERY FAILED.\n";
        } else {
            echo "done.\n";
        }
        return;
    }

    /**
     * correctJobDescription
     *
     * This function takes a value of T001F120_ACTUAL_JOB_CODE from the PRISM view, and converts it to the
     * value we're interested in plugging into the Fez Author table. This is inspired loosely by CK's
     * original CSV modding scripts.
     */
    function correctJobDescription($job_description) {

        if ($job_description == null) {
            $job_description = "Not Defined";
        } else {
            switch ($job_description) {
                case 'T&R':
                    $job_description = "Teaching & Research";
                    break;
                case 'ACAD':
                    $job_description = "Other";
                    break;
                case 'RO':
                    $job_description = "Research Only";
                    break;
                case 'EMSAH':
                    $job_description = "English, Media Studies and Art History";
                    break;
            }
        }
        return $job_description;
    }

    /**
     * gameOn
     *
     * This is the main function.
     */
    function gameOn() {
        $startTime = timerStart();
        echo "======================================================\n";
        echo "Running LDAP to Fez import and synchronisation utility\n";
        global $fezConn;
        $fezConn = LDAP2FEZ::initFezConnection();
        LDAP2FEZ::oracleTime();             // The main Oracle loop
        LDAP2FEZ::closeFezConnection();
        timerStop($startTime);
        echo "\nDone! Exiting...\n";
        return;

    }

    /**
     * initFezConnection
     *
     * Establish a database connection to the Fez Repository. We will need to push data into Fez,
     * and this is the connection we'll use to accomplish that.
     *
     * This function returns the connection handler, which can be referenced as a global elsewhere.
     */
    function initFezConnection() {

        // This is where we set up a secondary database connection, so we can look up any relevant materials in Fez
        echo "Establishing database connection to Fez repository... ";

        $host = FEZ_HOST;
        $database = FEZ_DB;
        $user = FEZ_USER;
        $pass = FEZ_PASSWD;

        // Establish DB connection
        $conn = @mysql_connect($host, $user, $pass);
        if (!$conn) {
            die("Error: Could not connect to MySQL database. Aborting.\n");
        }

        // Select the Fez database
        $db_selected = @mysql_select_db($database, $conn);
        if (!$db_selected) {
            die ("Can't use " . $database . " : " . mysql_error());
        }

        echo "done.\n";
        return $conn;

    }

    /**
     * closeFezConnection
     *
     * Everything is over and done with. Close down the connection to the Fez database.
     */
    function closeFezConnection() {

        global $fezConn;
        mysql_close($fezConn);

    }

}

/**
 * timerStart
 *
 * For basic benchmarking. Records the time that the program was invoked.
 */
function timerStart() {
    $starttime = microtime();
    $startarray = explode(" ", $starttime);
    return $startarray[1] + $startarray[0];
}

/**
 * timerStop
 *
 * For basic benchmarking. Records the time that the program was terminated, and reports total run time.
 */
function timerStop ($starttime) {
    $endtime = microtime();
    $endarray = explode(" ", $endtime);
    $endtime = $endarray[1] + $endarray[0];
    $totaltime = $endtime - $starttime;
    $totaltime = round($totaltime,5);
    echo "\nThis script executed in " . $totaltime . " seconds.";
}

/*
SQL for creating Oracle view

create view LIBRARY_STAFF_VIEW as
select T000F005_WAMI_KEY, T000F145_SURNAME_FOR_LETTERS, T000F020_GIVEN_NAMES, T000F025_PREFERRED_NAME, T000F030_SALUTATION, T000F160_EMAIL_ADDRESS, T001F070_ACTUAL_ORG_UNIT_NO, T001F120_ACTUAL_JOB_CODE, T900F015_SHORT_DESCRIPTION from staff_perm
union
select T000F005_WAMI_KEY, T000F145_SURNAME_FOR_LETTERS, T000F020_GIVEN_NAMES, T000F025_PREFERRED_NAME, T000F030_SALUTATION, T000F160_EMAIL_ADDRESS, T001F070_ACTUAL_ORG_UNIT_NO, T001F120_ACTUAL_JOB_CODE, T900F015_SHORT_DESCRIPTION from staff_casual;
*/

