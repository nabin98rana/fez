<?php

include_once(APP_INC_PATH.'class.background_process.php');
include_once(APP_INC_PATH . 'class.bookreaderpdfconverter.php');

class BackgroundProcess_Generate_Bookreader_Images extends BackgroundProcess
{
	protected $pdfConverter;

    function __construct()
	{
		parent::__construct();
		$this->include = 'class.bgp_generate_bookreader_images.php';
		$this->name = 'Generate Bookreader Images';
        $this->pdfConverter = new bookReaderPDFConverter();
	}

	function run()
	{
        $this->setState(BGP_RUNNING);
		extract(unserialize($this->inputs));

        $this->pdfConverter->setPIDQueue($pid, 'pdfToJpg');
        $this->pdfConverter->runQueue();
        $this->setState(BGP_FINISHED);
    }
}