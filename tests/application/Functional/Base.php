<?php

//require_once 'bootstrap.php';

class Functional_Base extends PHPUnit_Extensions_SeleniumTestCase
{
  /**
   * Default time out for wait* methods
   */
  const DEFAULT_TIMEOUT = 30000;
  
  public static $browsers = array(
    array(
        'browser' => '*firefox'
    ) //,
    /*array(
         'browser' => '*googlechrome'
    ),*/
  );

  /**
   * @var Zend_Config
   */
  protected $_config;

  /**
   * @var bool
   */
  protected $_destructive;

  public function __construct($name = NULL, array $data = array(), $dataName = '', array $browser = array())
  {
    parent::__construct($name, $data, $dataName, $browser);
    
    //@debug: we don't have Zend_Config used on Fez.
    // @todo: Not essential, use Zend_Config to handle Fez config.
    //        Fez configurations can be found on these files:
    //        - config.inc.php, 
    //        - init.php, 
    //        - class.configuration.php (which also loads db-based config)
//    $this->_config = Zend_Registry::get('config');
    
    // Is this destructive or non-destructive test?
    $this->_destructive = ($this->_config->testing->destructive == 1) ? TRUE : FALSE;
  }
   
  
  protected function setUp()
  {
    $this->setBrowser(self::$browsers[0]["browser"]);
    $this->setBrowserUrl($this->_config->testing->url);
    $this->setTimeout(self::DEFAULT_TIMEOUT);
  }

//  /**
//   * Logs in a user who belongs to the specified role
//   *
//   * @param string $role The name of the role the user should belong to
//   */
//  protected function login($role)
//  {
//    $this->open("/");
//    $max = (time() + 86400);
//    $this->createCookie(
//        "{$this->_config->testing->cookie_name}=$role",
//        "path=/,max_age=$max,domain={$this->_config->testing->cookie_domain}"
//    );
//  }
}
