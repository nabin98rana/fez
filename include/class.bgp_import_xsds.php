<?php

include_once(APP_INC_PATH.'class.background_process.php');
include_once(APP_INC_PATH.'class.doc_type_xsd.php');

class BackgroundProcess_Import_XDSs extends BackgroundProcess
{
    function __construct() 
    {
        parent::__construct();
        $this->include = 'class.bgp_import_xsds.php';
        $this->name = 'Import XSDs';
    }

    function run()
    {
        $this->setState(1);
        extract(unserialize($this->inputs));
        Doc_Type_XSD::importXSDs($filename, $xdis_ids, $this);
        unlink($filename);
        $this->setState(2);
    }
}



?>
