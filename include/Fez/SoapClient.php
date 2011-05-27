<?php

class Fez_SoapClient extends SoapClient
{  
  public function __construct($wsdl, $options) {
    parent::__construct($wsdl, $options);
  }
  
  public function __doRequest($request, $location, $action, $version)
  { 
    return parent::__doRequest($request, $location, $action, $version);
  }
}