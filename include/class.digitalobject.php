<?php

/*

The part of the array produced by Record::updateSearchKeys 
looks like this:

'file_attachment_name' => 
    array (
      'xsdmf_id' => 6166,
      'xsdmf_value' => 
      array (
        0 => 'Arianrhod_Chapte.pdf',
        1 => 'cmtest1.pdf',
        2 => 'presmd_Arianrhod_Chapte.xml',
        3 => 'presmd_cmtest1.xml',
      ),
    )
    
When we instatiate a DSResource for each of the files
this will be the file name data used to make hashes 
for the CAS, etc.

 */

class DigitalObject
{
    /**
     * The object's DB connection.
     * @var <Zend_Db>
     */
    private $db;
    
    /**
     * Metadata for a digital object.
     * @var <array>
     */
    private $pidData;
    
    /**
     * The currentn PID
     * @var <string>
     */
    private $pid;
    
    /**
     * A collection of this PID's loaded datastreams.
     * @var <array>
     */
    private $dataStreams;
    
    /**
     * A DSResource to handle datastreams.
     * @var <DSResource>
     */
    private $dsResource;
    
    /**
     * The next PID should be at least this
     * to prevent clashes with existing pids.
     */
    const PID_NO_CLASH = 200000;
    
    /**
     * Set up the object.
     */
    public function __construct()
    {
        $this->db = DB_API::get();
        $this->dsResource = new DSResource();
    }
    
    /**
     * Insert or update a digital object 
     * and generate a PID if required. 
     * Updates if PID provided otherwise inserts.
     * @param <array> $objdata
     */
    public function save($objdata)
    {
        //TODO returns, wrap in transaction, try/catch.
        if(array_key_exists('pid', $objdata))
        {
            $this->pid = $objdata['pid'];
            
            list($pidns, $pidint) = explode(":", $objdata['pid']);
            
            unset($objdata['pid']); //Exclude the pid from the update.
            $updateFields = $this->setUpdateFields($objdata);
            
            $sql = "UPDATE " . APP_TABLE_PREFIX . "digital_object SET " 
                . $updateFields['set'] . " WHERE pidns = :pidns AND pidint = :pidint";
                
            //Bind pidint and pidns to the WHERE in the update.
            $updateFields['binding'][':pidns'] = $pidns;
            $updateFields['binding'][':pidint'] = $pidint;
            
            $this->db->query($sql, $updateFields['binding']);
        }
        else
        {
            //If no namespace, use the one in the config.
            $pidns = ($objdata['pidns']) ? $objdata['pidns'] : APP_PID_NAMESPACE;
            
            $sql = "SELECT MAX(pidint)+1 AS pidint FROM " . APP_TABLE_PREFIX 
                . "digital_object WHERE pidns = :pidns";
            $stmt = $this->db->query($sql, array(':pidns' => $pidns));
            $pidint = $stmt->fetch();
            
            //Check to see if this the first pid for this namespace.
            $pidint = ($pidint['pidint'] == NULL) ? 1 : $pidint['pidint']; 
            $pidint = ($pidint < self::PID_NO_CLASH) ? self::PID_NO_CLASH : $pidint;
            
            $this->pid = $pidns . ":" . $pidint;
            
            $objdata['pidint'] = $pidint;
            $objdata['pidns'] = $pidns;
            
            $insert = $this->setInsertFields($objdata);
            
            $sql = "INSERT INTO " . APP_TABLE_PREFIX . "digital_object " . $insert['insert'];
            $this->db->query($sql, $insert['binding']);
        }
        
        return $this->pid;
    }
    
    /**
     * Set up database fields for update. 
     * Handles varying numbers of fields.
     * @param <array> $fields
     */
    protected function setUpdateFields($fields)
    {
        $set = '';
        $binding = array();
        foreach($fields as $fieldk => $fieldv)
        {
            $set .= $fieldk . " = :" . $fieldk . ",";
            $binding[':' . $fieldk] = $fieldv;
        }
        
        return array('set' => $set, 'binding' => $binding);
    }
    
    /**
     * Set up database fields for insert.
     * Handles varying numbers of fields.
     * @param <array> $data
     */
    protected function setInsertFields($data)
    {
        $fields = array();
        $tokens = array();
        $binding = array();
        
        foreach($data as $datak => $datav)
        {
            $fields[] = $datak;
            $tokens[] = ':'.$datak;
            $binding[':'.$datak] = $datav;
        }
        
        $tokens = '(' . implode(',', $tokens) . ')';
        $fields = '(' . implode(',', $fields) . ')';
        $insert = $fields . ' VALUES ' . $tokens;
        
        return array('insert' => $insert, 'binding' => $binding);
    }
    
    /**
     * Return all metadata for a PID
     * @param <string> $pid
     */
    public function get($pid)
    {
        list($pidns, $pidint) = explode(':', $pid);
        
        $sql = "SELECT * FROM " . APP_TABLE_PREFIX . "digital_object WHERE"
            . " pidns = :pidns AND pidint = :pidint";
        $stmt = $this->db->query($sql, array(':pidns' => $pidns, ':pidint' => $pidint));
        return $stmt->fetch();
    }
    
    /**
     * Load metadata into the object
     * @param <string> $pid
     */
    public function load($pid)
    {
        $pidData = $this->get($pid);
        $pidData['pid'] = $pid;
        $this->pidData = $pidData;
    }
    
    /**
     * Get all the datastreams for a PID
     * @param <array> $params
     */
    public function getDatastreams($params)
    {
        $dsList = $this->dsResource->listStreams($params['pid']);
        $datastreams = array();
        
        foreach($dsList as $datastream)
        {
            $dsFormatted = array();
            $dsRev = $this->dsResource->getDSRev($datastream['filename'], $datastream['pid']);
            $dsFormatted['controlGroup'] = $dsRev['controlgroup'];
            $dsFormatted['ID'] = $dsRev['filename'];
            $dsFormatted['versionID'] = $dsRev['version'];
            $dsFormatted['label'] = $dsRev['filename'];
            $dsFormatted['filename'] = $dsRev['filename'];
            $dsFormatted['versionable'] = false;
            $dsFormatted['MIMEType'] = $dsRev['mimetype'];
            $dsFormatted['formatURI'] = 'unknown';
            $dsFormatted['createDate'] = $dsRev['version'];
            $dsFormatted['size'] = $dsRev['size'];
            $dsFormatted['state'] = $dsRev['state'];
            $dsFormatted['location'] = '';
            //$dsFormatted['checksumType'] = $datastream['controlgroup'];
            //$dsFormatted['checksum'] = $datastream['controlgroup'];
            //$dsFormatted['altIDs'] = $datastream['controlgroup'];
            
            $datastreams[] = $dsFormatted;
        }
        
        return $datastreams;
        //$data = var_export($datastreams,true);
        //file_put_contents('/var/www/fez/tmp/fedoraOut.txt', "\n".__METHOD__." | ".__FILE__." | ".__LINE__." >>>> ".$data, FILE_APPEND);
        
        //$parms=array('pid' => $pid, 'asOfDateTime' => $createdDT, 'dsState' => $dsState);
        /*$this->load($params['pid']);
        
        $sql = "SELECT fm.mimetype as MIMEType, fm.controlgroup as controlGroup, fa.size, fm.state, "
        . "fa.filename as ID, MAX(fa.version) as versionID, MAX(fa.version) as createDate "
        . "FROM " . APP_TABLE_PREFIX . "file_meta fm, " . APP_TABLE_PREFIX . "file_attachments fa "
        . "WHERE fm.id = fa.metaid AND fm.pid = :pid GROUP BY filename";
        $stmt = $this->db->query($sql, array(':pid' => $params['pid']));
        
        //return $stmt->fetchAll();
        $out = $stmt->fetchAll();
        $data = var_export($out,true);
        file_put_contents('/var/www/fez/tmp/fedoraOut.txt', "\n".__METHOD__." | ".__FILE__." | ".__LINE__." >>>> ".$data, FILE_APPEND);
        return $out;*/
    }
}