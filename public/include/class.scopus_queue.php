<?php
/**
 *
 * @author Chris Maj <c.majw@library.uq.edu.au>
 * @version 1.0, October 2012
 */

include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");
include_once(APP_INC_PATH . "class.queue.php");
include_once(APP_INC_PATH . "class.bgp_scopus.php");
include_once(APP_INC_PATH . "class.eventum.php");
include_once(APP_INC_PATH . "class.date.php");
include_once(APP_INC_PATH . "class.scopus_service.php");
include_once(APP_INC_PATH . "class.scopus_record.php");
include_once(APP_INC_PATH . "class.record_import.php");
include_once(APP_INC_PATH . "class.wos_record.php");
include_once(APP_INC_PATH . "class.org_structure.php");
include_once(APP_INC_PATH . "class.matching_conferences.php");
include_once(APP_INC_PATH . "class.mail.php");

class ScopusQueue extends Queue
{
    protected $_bgp;
    protected $_bgp_details;
    protected $_batch_size;
    // If we've registered the commit shutdown function
    protected $_commit_shutdown_registered;
    
    protected $_service;
  
    /**
     * Returns the singleton queue instance.
     * @return instance of class Scopus
     */
    public static function get() 
    {
        $log = FezLog::get();
        
        try
        {
          $instance = Zend_Registry::get('Scopus');
        }
        catch(Exception $ex)
        {
            // Create a new instance
            $instance = new ScopusQueue();
            $instance->_use_locking = false;
            $instance->_dbqp = 'spq_';
            $instance->_dbtp = APP_TABLE_PREFIX . 'scopus_';
            $instance->_service = new ScopusService(APP_SCOPUS_API_KEY);
            Zend_Registry::set('Scopus', $instance);
        }
        return $instance;
    }
    
    /**
     * Processes the queue.
     */
    protected function process() 
    {
        $log = FezLog::get();
            
        $bgp = new BackgroundProcess_Scopus();
        // TODO: maybe take something other than admin
        $bgp->register(serialize(array()), APP_SYSTEM_USER_ID);
    }
    
    /**
     * Links this instance to a corresponding background process started above
     *
     * @param BackgroundProcess_LinksAmr $bgp
     */
    public function setBGP(&$bgp) 
    {
        $this->_bgp = &$bgp;
    }
    
    /**
     * Get a list of UQ Scopus IDs owned by UQ
     * from the last 30 days and push them into 
     * the queue table.
     */
    public function prepareQueue()
    {
        $xml = $this->_service->getNextRecordSet();
        
        while($this->_service->getRecSetStart())
        {
            $doc = new DOMDocument();
            $doc->loadXML($xml);
            $records = $doc->getElementsByTagName('identifier');
            
            foreach($records as $record)
            {
                $scopusId = $record->nodeValue;
                $matches = array();
                preg_match("/^SCOPUS_ID\:(\d+)$/", $scopusId, $matches);
                $scopusIdExtracted = (array_key_exists(1, $matches)) ? $matches[1] : null;
                
                $this->add($scopusIdExtracted);
            }
            
            $this->commit();
            $xml = $this->_service->getNextRecordSet();
        }
    }

    /**
    * Processes the queue in the background.    
    */
    public function bgProcess() 
    {
        $log = FezLog::get();
        
        $this->_bgp->setStatus("Scopus queue processing started");
        $recCount = 0;
        
        $nameSpaces = array(
                            'prism' => "http://prismstandard.org/namespaces/basic/2.0/",
                            'dc' => "http://purl.org/dc/elements/1.1/",
                            'opensearch' => "http://a9.com/-/spec/opensearch/1.1/"
        );
        
        do
        {
            $scopRec = $this->pop();
            $scopusId = $scopRec[$this->_dbqp.'id'];
            
            if($scopRec)
            {
                $csr = new ScopusRecItem();
                $rec = $this->_service->getRecordByScopusId($scopusId);
                $csr->load($rec, $nameSpaces);
                $csr->liken();
                $recCount++;
            }
        }
        while($scopRec);
        $this->_bgp->setStatus("Scopus queue processing finished");
        
        return $recCount;
    }
}
