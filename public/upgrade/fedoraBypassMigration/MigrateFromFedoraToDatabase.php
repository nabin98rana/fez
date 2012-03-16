<?php

// +----------------------------------------------------------------------+
// | Fez - Digital Repository System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2005, 2006, 2007 The University of Queensland,         |
// | Australian Partnership for Sustainable Repositories,                 |
// | eScholarship Project                                                 |
// |                                                                      |
// | Some of the Fez code was derived from Eventum (Copyright 2003, 2004  |
// | MySQL AB - http://dev.mysql.com/downloads/other/eventum/ - GPL)      |
// |                                                                      |
// | This program is free software; you can redistribute it and/or modify |
// | it under the terms of the GNU General Public License as published by |
// | the Free Software Foundation; either version 2 of the License, or    |
// | (at your option) any later version.                                  |
// |                                                                      |
// | This program is distributed in the hope that it will be useful,      |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of       |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        |
// | GNU General Public License for more details.                         |
// |                                                                      |
// | You should have received a copy of the GNU General Public License    |
// | along with this program; if not, write to:                           |
// |                                                                      |
// | Free Software Foundation, Inc.                                       |
// | 59 Temple Place - Suite 330                                          |
// | Boston, MA 02111-1307, USA.                                          |
// +----------------------------------------------------------------------+

/**
 * This script is to allow smooth migration on Fez system 
 * from Fedora based record storaged to non-Fedora (database based).
 * It script ONLY supports removing Fedora for good, it is NOT intended for migrating the other way around.
 * 
 * It executes individual migration scripts, such as: 
 * - build/alter database schema, 
 * - migrates existing record from Fedora XML, 
 * - migrates attached files, 
 * - runs sanity checking or run related test cases 
 *
 * @version 1.0, 2012-03-08
 * @author Elvi Shu <e.shu at library.uq.edu.au>
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright (c) 2012 The University of Queensland
 * 
 * @example ./migrate.php Example class usage
 */
include_once(APP_INC_PATH . "class.upgrade.php");
include_once(APP_INC_PATH. 'class.bgp_index_object.php');
include_once(APP_INC_PATH. 'class.reindex.php');


        
class MigrateFromFedoraToDatabase
{
    
    protected $_log = null;
    protected $_db  = null;
    protected $_shadowTableSuffix = "__shadow";
    protected $_upgradeHelper     = null;
    
    protected $_config = null;
    
    
    public function __construct($config = array())
    {
        $this->_config = new stdClass();
        $this->_log = FezLog::get();
        $this->_db = DB_API::get();
        $this->_upgradeHelper = new upgrade();
        
        $this->_parseConfig($config);
        
        // Message/warning about the checklist required before running the migration script.
        $this->preMigration();
        
        // Updating the structure of Fedora-less
//        $this->stepOneMigration();
        
        // Content migration
        $this->stepTwoMigration();
        
        // Security recalculation
        $this->stepThreeMigration();
    }

    
    /**
     * Parse class configuration param.
     * @param array $config
     * @return boolean
     */
    protected function _parseConfig($config = array())
    {
        if (!is_array($config)){  
            // Get lost
            return false;
        }
        
        foreach ($config as $cfg){
            $cfg = explode("=", $cfg);
            if (!array_key_exists(0, $cfg) || !array_key_exists(1, $cfg)){
                continue;
            }

            $key   = $cfg[0];
            $value = $cfg[1];
            
            switch ($key){
                case 'autoMapXSDFields':
                    $this->_config->$key = (bool)$value;
                    break;
            }
        }
    }
    
    
    /**
     * This step is to inform/warn/scare web administrator on what they are about to do.
     */
    public function preMigration()
    {
        echo chr(10) . "<br /> Before running this migration script, 
            please make sure you have gone through the following checklist. 
            There is no way to revert the system once this script executed, 
            so make sure you have backup system to rollback to in the case of migration failure.
            <ul>
                <li> Merged fedora_bypass branch to the trunk. </li>
                <li> BACKUP your DATABASE, and extra BACKUP for rollback.</li>
                <li> Did we mention BACKUP? </li>
                <li> Mapped all the XSD fields to the search keys accordingly. 
                     Refer to mapXSDFieldToSearchKey method for sample query </li>
                <li> ... </li>
            </ul>
         ";

        // Executes mapXSDFields methods, when specified. 
        // This is considering that the sk on mapping methods match your system.
        // This method is created to support UQ eSpace Fez.
        if (property_exists($this->_config, 'autoMapXSDFields') && $this->_config->autoMapXSDFields === true) {
            $this->mapXSDFieldToSearchKey();
        }
    }
    
    
    /**
     * First round of migration, updating database schema.
     */
    public function stepOneMigration()
    {
        
        // Create shadow tables for search keys
        $this->createSearchKeyShadowTables();
         
        // Create Digital Object tables
        $this->createDigitalObjectTables();
        
        // Sets the maximum PID on PID index table.
        $this->setMaximumPID();
        
    }
    
    
    /**
     * Second round of migration, migrates existing content.
     * We could separate this into two steps:
     * 1. on all PIDS & managedContent, without outage.
     * 2. on updated PIDs / managedContents, after the #1 step.
     */
    public function stepTwoMigration()
    {
        // Migrate all records from Fedora
        $this->migratePIDs();
        
        // Datastream (attached files) migration 
        $this->migrateManagedContent();
    }
    
    
    /**
     * Third round of migration, at this stage we should have the records copied/migrated.
     * This stage we are doing touching up on the records, such as:
     * - Security 
     * - ....
     */
    public function stepThreeMigration()
    {
        // Security for PIDs
        include_once("../../misc/migrate_security_recalculate.php");
    }
    
    
    /**
     * This method automatically map XSD fields that we can manually find. 
     * Some manual mapping will be required depending on the XSD field on your Fez application.
     */
    public function mapXSDFieldToSearchKey()
    {
     
        $this->_mapSubjectField();
        
        // @todo: need to find out what search key should we map the Q Index code field.
//        $this->_mapQIndexCode();  
    }
    
    
    protected function _mapQIndexCode()
    {
        // Q-index code, Q-index status, Institutional status, collection year and year available dropdowns are not being saved.        
        /*        
        SELECT xdis_title, fez_xsd_display_matchfields.* 
        FROM fez_xsd_display_matchfields
        LEFT JOIN fez_xsd_display ON xsdmf_xdis_id = xdis_id
        WHERE xsdmf_html_input = 'contvocab_selector' AND xsdmf_sek_id IS NULL;
        */
        
        // Q-index code = HERDC Code ? 
        // Institutional Status
        
        /*
        -- Query to find Subject Controlled Vocab  XSD fields that do not have search keys --
        SELECT xdis_title, fez_xsd_display_matchfields.* 
        FROM fez_xsd_display_matchfields
        LEFT JOIN fez_xsd_display ON xsdmf_xdis_id = xdis_id
        WHERE xsdmf_title LIKE "%subject%"
        AND xsdmf_html_input = 'contvocab_selector';
        */
        
        $stmt = " UPDATE ". APP_TABLE_PREFIX ."xsd_display_matchfields
                    SET xsdmf_sek_id = (SELECT sek_id FROM ". APP_TABLE_PREFIX ."search_key WHERE sek_title = 'Subject')
                    WHERE xsdmf_title LIKE '%subject%'
                    AND xsdmf_html_input = 'contvocab_selector';";
        
        try {
            $this->_db->exec($stmt);
        } catch (Exception $ex) {
            echo "<br />Failed to map XSD field. Here is why: ". $stmt . " <br />" . $ex .".\n";
            return false;
        }
        return true;
    }


    protected function _mapSubjectField()
    {
        // Check if Subject XSD fields have been mapped
        $stmt = "SELECT COUNT(xsdmf_id) as howmany 
                 FROM ". APP_TABLE_PREFIX ."xsd_display_matchfields
                 LEFT JOIN ". APP_TABLE_PREFIX ."xsd_display ON xsdmf_xdis_id = xdis_id
                 WHERE xsdmf_title LIKE '%subject%'
                    AND xsdmf_html_input = 'contvocab_selector'
                    AND (xsdmf_sek_id IS NULL OR xsdmf_sek_id = '');";
        
        $unmappedFields  = $this->_db->fetchRow($stmt);

        
        // Run update query for unmapped Subject fields
        if (array_key_exists('howmany', $unmappedFields) && $unmappedFields['howmany'] > 0){
            
            $stmt = "UPDATE ". APP_TABLE_PREFIX ."xsd_display_matchfields
                     SET xsdmf_sek_id = (SELECT sek_id FROM ". APP_TABLE_PREFIX ."search_key WHERE sek_title = 'Subject')
                     WHERE xsdmf_title LIKE '%subject%'
                        AND xsdmf_html_input = 'contvocab_selector'
                        AND (xsdmf_sek_id IS NULL OR xsdmf_sek_id = '');";

            try {
                $this->_db->exec($stmt);
                echo chr(10) . "<br /> Successfully mapped subject " . print_r($unmappedFields, 1);
            } catch (Exception $ex) {
                echo "<br />Failed to map XSD field. Here is why: ". $stmt . " <br />" . $ex .".\n";
                return false;
            }
        }
        return true;
    }

    
    /**
     * Returns an array of unmapped XSD fields.
     * @return array | boolean 
     */
    protected function _getUnmappedXSDFields()
    {
        $excludeInput   = array('static', 'xsd_ref'); 
        $excludeDisplay = array('FEZACML for Datastreams', 'FezACML for Communities', 
                                'FezACML for Collections', 'FezACML for Records', 
                                'DesignRQF2006MD Display', 'MARCXML test record');
        
        $stmt = "SELECT xdis_title, fez_xsd_display_matchfields.* 
                    FROM fez_xsd_display_matchfields
                    LEFT JOIN fez_xsd_display ON xsdmf_xdis_id = xdis_id

                    WHERE xsdmf_enabled = 1 AND xsdmf_invisible = 0 
                    AND xsdmf_html_input NOT IN (". explode(", ", $excludeInput) .")
                    AND xsdmf_sek_id IS NULL
                    AND xdis_title NOT IN (". explode(", ", $excludeDisplay) .");
                    ";
        try {
            $unmappedFields = $this->_db->fetchAll($stmt);
            return $unmappedFields;
        } catch (Exception $ex) {
            echo "<br />Failed to grab unmapped fields because of: ". $stmt . " <br />" . $ex .".\n";
        }
        return false;
    }
    
    
    /**
     * Run Fedora managed content migration script & security for the attached files.
     * @todo: update misc/migrate_fedora_managedcontent_to_fezCAS.php to return, instead of exit at the end of the script.
     * 
     * @todo:
     * To speed up this process, specially for system that already running CAS system, 
     * we going to do a checksum, compare the new md5 with "existing" md5 if any.
     * 
     */
    public function migrateManagedContent()
    {
        include ("../../misc/migrate_fedora_managedcontent_to_fezCAS.php");
    }
    
    
    /**
     * Call bulk 'reindex' workflow on all PIDS.
     * We could runs this function before the outage.
     * eSpace has around 165K records, so here we are, running it in phases.
     */
    public function migratePIDs()
    {
        $stmt = "SELECT COUNT(rek_pid) FROM " . APP_TABLE_PREFIX . "record_search_key
                 ORDER BY rek_pid DESC ";
        
        try{
            $totalPids = $this->_db->fetchCol($stmt);
        }catch (Exception $e){
            echo chr(10) . "<br /> Failed to retrieve total pids. Query: " . $stmt;
            return false;
        }
        
        $limit = 10;  // how many pids per process
        $loop  = 2;  // how many times we want to loop
        for($i = 0; $start < $totalPids && $i < $loop; $i++){
            if ($i == 0){
                $start = $i;
            }
            $this->_reindexPids($start, $limit); 
            $start += $limit;
        }
        
    }

    /**
     * Run Reindex workflow on an array of pids
     * @return boolean 
     */
    protected function _reindexPids($start, $limit)
    {
        $wft_id  = 277;  // hack: Reindex workflow trigger ID
        $pid     = "";
        $xdis_id = "";
        $href    = "";
        $dsID    = "";
        
        // Test PID for reindex
        /*
        $pids    = array("UQ:87692");
        Workflow::start($wft_id, $pid, $xdis_id, $href, $dsID, $pids);
        */
        
        $stmt = "SELECT rek_pid 
                 FROM " . APP_TABLE_PREFIX . "record_search_key
                 ORDER BY rek_pid DESC 
                 LIMIT ". $start .", ". $limit .";";
        
        try{
            $pids = $this->_db->fetchCol($stmt);
        }catch (Exception $e){
            echo chr(10) . "<br /> Failed to retrieve pids. Query: " . $stmt;
            return false;
        }

        
        if ( sizeof($pids) > 0 ){
            Workflow::start($wft_id, $pid, $xdis_id, $href, $dsID, $pids);
            
            echo chr(10) . "<br /> BGP of Reindexing the PIDS has been triggered. 
                 See the progress at http://" . APP_HOSTNAME . "/my_processes.php";
        }
        return true;
    }
    
    
    /**
     * Recalculate security.
     */
    public function recalculateSecurity()
    {
        include ("../../misc/migrate_security_recalculate.php");
    }
    
    
    /**
     * Sets the maximum PID on PID index table.
     * Based on rm_fedora.php
     * 
     * @return boolean 
     */
    public function setMaximumPID()
    {
        // Get the maximum PID number from Fedora
        $nextPID = Fedora_API::getNextPID(false);
        $nextPIDParts = explode(":", $nextPID);
        $nextPIDNumber = $nextPIDParts[1];
        
        // Make sure we have the pid index table
        $tableName = APP_TABLE_PREFIX . "pid_index";
        if (!$this->_isTableExists($tableName)){
            
            echo "Creating pid_index table ... ";
            
            $stmt = "CREATE TABLE ". $tableName ." 
                       (pid_number int(10) unsigned NOT NULL, 
                        PRIMARY KEY (pid_number)
                       );";
            try {
                $this->_db->exec($stmt);
            } catch (Exception $ex) {
                echo "<br />Table ". $tableName ." creation failed. Here is why: ". $stmt . " <br />" . $ex .".\n";
                return false;
            }
        }
        
        // Insert the maximum PID
        echo "Fetching next PID from Fedora, and writing to pid_index table ... ";
        $stmt = "INSERT INTO ". $tableName ." (pid_number) VALUES ('" . $nextPIDNumber . "');";
        $this->_db->exec($stmt);
        echo "ok!\n";
    }
    
    
    /**
     * Upgrade table schema for all datastream permissions and pid non inherited permissions
     */
    public function updateForDatastreamPermission()
    {
        $file = "upgrade/sql_scripts/upgrade2012021700.sql";
        
        $this->_upgradeHelper->parse_mysql_dump($file);
    }
    

    /**
     * Create tables and update db schema to store Digital Object & attached files.
     */
    public function createDigitalObjectTables()
    {
        
        // Run this script: upgrade2012031200.sql
        // Creates digital object table.
        $file = "upgrade/sql_scripts/upgrade2012031200.sql";
        $this->_upgradeHelper->parse_mysql_dump($file);
        
        // Run this script: upgrade2012022100.sql
        // Creates file_attachments table and its shadow table.
        $file = "upgrade/sql_scripts/upgrade2012022100.sql";
        $this->_upgradeHelper->parse_mysql_dump($file);
        
        // Run this script: upgrade2012022101.sql
        // Alter file_attachments table, add a file to indicate whether security is inherited column for the datastreams.
        $file = "upgrade/sql_scripts/upgrade2012022101.sql";
        $this->_upgradeHelper->parse_mysql_dump($file);
        
//        try{
//            $this->_db->exec(implode(' ', $stmt));
//            return true;
//        } catch(Exception $e) {
//            echo "<br> Failed creating Digital Object tables. Stmt = ". $stmt . " Ex: " . $ex;
//        }
//        return false;
    }
    
    
    /**
     * This script is based on rm_fedora.php. 1.1 - 1.4, 1.6 - 1.7
     * Creates shadow tables for core search key tables and each non-core search key tables.
     * Remove any unique constraints from the shadow table.
     * 
     * @return boolean 
     */
    public function createSearchKeyShadowTables()
    {
        // 1.1 Create core search key shadow table
        echo "<br />1.1 Creating core search key shadow table ... ";
        
        $originalTable = APP_TABLE_PREFIX . "record_search_key";
        
        // Create the table
        if (!$this->_createOneShadowTable($originalTable)){
            return false;
        }
        
        // Remove any unique keys copied from the search key table from the shadow table
        $this->_removeUniqueConstraintsCore();

        // Add a joint primary key
        $this->_addJointPrimaryKeyCore();
        
        echo "<br /> End of 1.1. Now we have shadow for core search key shadow table ".$originalTable;

        
        
        // 1.2 Create non-core search key shadow tables
        echo "<br />1.2 Creating non-core search key shadow tables ... \n";
        
        $searchKeys = Search_Key::getList();
        foreach ($searchKeys as $sk) {
            
            // We only create search key table with multiple relationship
            if ($sk['sek_relationship'] != '1') {
                continue;
            }
            
            echo "<br /> Shadowing " . $sk['sek_title_db'] . " table ... ";

            $originalTable = APP_TABLE_PREFIX . "record_search_key_" . $sk['sek_title_db'];
            $shadowTable = APP_TABLE_PREFIX . "record_search_key_" . $sk['sek_title_db'] . $this->_shadowTableSuffix;
            
            // Create the table
            if (!$this->_createOneShadowTable($originalTable, $sk['sek_title_db'])){
                return false;
            }
            
            // Remove any unique keys copied from the search key table from the shadow table
            $this->_removeUniqueConstraintsNonCore($shadowTable);
            
            // Add joint primary key
            $this->_addJointPrimaryKeyNonCore($shadowTable, $sk['sek_title_db']);
            
            echo "<br /> End of Shadowing " . $sk['sek_title_db'] . " table.. with a SuCCeSS!";
        }
        
        echo "<br /> End of 1.2. Now we have shadow tables for non-core search keys.";
    }
    
    
    /**
     * Creates search key shadow table with matching schema with the original sk table.
     * Adds timestamp column on the shadow table record versioning.
     * @todo: Update to use CREATE TABLE IF NOT EXISTS instead.
     * 
     * @param string $originalTable Name of the original search key table.
     * @return boolean True when shadow table successfully created 
     */
    protected function _createOneShadowTable($originalTable, $sekTitleDb="")
    {
        if (empty($originalTable) || is_null($originalTable)){
            return false;
        }
        
        $shadowTable = $originalTable . $this->_shadowTableSuffix;
        
        // Creates table duplicate from original sk table
        // @todo: Update to use CREATE TABLE IF NOT EXISTS instead.
        if ( !$this->_isTableExists($shadowTable) ){
            
            $stmt = "CREATE TABLE ". $shadowTable ." LIKE ". $originalTable;
            
            try {
                $this->_db->exec($stmt);
            } catch (Exception $ex) {
                echo "<br />Table ". $shadowTable ." creation failed. Here is why: ". $stmt . " <br />" . $ex .".\n";
                return false;
            }
            
            echo "<br />Table ". $shadowTable ." has been created.\n";
            
        
            // Add stamp column to new shadow table
            echo "<br />Adding stamp column to the new shadow table ... ";

            $tableDescribe = $this->_db->describeTable($shadowTable);
            $columnName = "rek_" . (!empty($sekTitleDb) ? $sekTitleDb . "_" : "" ) . "stamp";

            if ( !array_key_exists("rek_stamp", $tableDescribe) ) {

                $stmt = "ALTER TABLE ". $shadowTable ." ADD COLUMN ". $columnName ." DATETIME;";

                try {
                    $this->_db->exec($stmt);
                } catch (Exception $ex) {
                    echo "<br />Alter table failed. Because of: ". $stmt . " <br />" . $ex;
                    return false;
                }

                echo "<br />Table ". $shadowTable ." has been altered.\n";

            } else {
                echo "<br />We have the stamp! Move on...";
            }

        }else {
            echo "<br />Table ". $shadowTable ." already exists somewhere in the universe, let's move on...\n";
        }

        return true;
    }

    
    protected function _removeUniqueConstraintsCore()
    {
        // Core search key shadow table
        echo chr(10) . "<br />" . "Removing unique constraint from fez_record_search_key__shadow ... ";
        
        $tableName = APP_TABLE_PREFIX . "record_search_key" . $this->_shadowTableSuffix;
        
        $stmt = "SHOW INDEX FROM ". $tableName ." WHERE Non_unique = 0";
        $uniqueIndex = $this->_db->fetchRow($stmt);
        
        if (is_array($uniqueIndex) && sizeof($uniqueIndex)>0){
            $stmt = "DROP INDEX unique_constraint ON ". $tableName .";";
            try {
                $this->_db->exec($stmt);
            } catch (Exception $ex) {
                echo chr(10) . " <br /> No constraint to remove. " . $stmt ;
            }
            echo "ok!\n";
        }
        
        
        // We are removing primary key on shadow because PID is serving as primary key on the core search key table.
        echo "* Removing primary key constraint from fez_record_search_key__shadow ... ";
        $stmt = "ALTER TABLE ". $tableName ." DROP PRIMARY KEY;";
        try {
            $this->_db->exec($stmt);
        } catch (Exception $ex) {
            echo "<br />No constraint to remove " .$stmt . " - Exception=" . $ex;
            return false;
        }
        echo "ok!\n\n";
    }
    
    
    // 1.6 Remove unique constraints from non-core shadow tables
    protected function _removeUniqueConstraintsNonCore($tableName)
    {
        echo "* Removing unique constraints from ". $tableName;

        $stmt = "DROP INDEX unique_constraint ON ". $tableName .";";
        
        try {
            $this->_db->exec($stmt);
        } catch (Exception $ex) {
            echo "<br />No constraint to remove " . $ex;
            return false;
        }
        echo "ok!\n";
        
        return true;
    }
        
    
    // 1.8 Add joint primary keys to shadow tables
    protected function _addJointPrimaryKeyCore()
    {
        $tableName = APP_TABLE_PREFIX . "record_search_key" . $this->_shadowTableSuffix;
        
        echo " Adding joint primary key to fez_record_search_key__shadow";
        $stmt = "ALTER TABLE ". $tableName ." DROP PRIMARY KEY, ADD PRIMARY KEY (rek_pid, rek_stamp);";
        try {
            $this->_db->exec($stmt);
        } catch (Exception $ex) {
            echo "<br />Could not add joint primary key to ". $tableName . " because: " . $ex;
            return false;
        }
        echo "\n";
    }

    

    protected function _addJointPrimaryKeyNonCore($tableName, $sekTitleDB)
    {
        
        echo " Adding joint primary key to ". $tableName;
        $stmt = "ALTER TABLE ". $tableName ."
                 ADD UNIQUE KEY (rek_" . $sekTitleDB . "_pid, rek_" . $sekTitleDB . "_stamp);";
        try {
            $this->_db->exec($stmt);
        } catch (Exception $ex) {
            echo "<br />Could not add joint primary key to ". $tableName . " because: " . $ex;
            return false;
        }
        echo "\n";
    }
    
    
    /**
     * Check if table already exists on currently connected database.
     * 
     * @param string $tableName Name of the table, dooh...
     * @return boolean By default return FALSE. Return TRUE only when table exists. 
     */
    protected function _isTableExists($tableName)
    {
        try{
            $exists = $this->_db->describeTable($tableName);
            if (!empty($exists)) {
                return true;
            }
        }catch(Exception $e){
            // nothing to see here, return false on next line
        }
        return false;
    }
}
