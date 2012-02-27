<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
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
// | Authors: Christiaan Kortekaas <c.kortekaas@library.uq.edu.au>,       |
// |          Matthew Smith <m.smith@library.uq.edu.au>                   |
// +----------------------------------------------------------------------+
//
//


/**
 * Class to manage all tasks related to the cache abstraction module.
 *
 * @version 1.0
 * @author João Prado Maia <jpm@mysql.com>
 */

class FezCache
{

    /**     
     * @access public
     */
    function FezCache()
    {
    	
    }
    
	/**
     * 
     * @return Zend_Cache_Core|Zend_Cache_Frontend|bool
     */
    public static function get($index = 'cache') 
    {
    	$cache = false;
    	 
		if (!class_exists("Zend_Registry")) {
			die("Zend could not be found. Either Zend is incorrectly installed, or you need to refactor your Fez config file.");
		}
		if($GLOBALS['app_cache']) {						
			try {
				$cache = Zend_Registry::get($index);
			}
			catch (Exception $ex) {}
		}
		
		return $cache;
    }
    
    /**
     * Remove a cache
     *
     * @param  string $id Cache id to remove
     * @return boolean True if ok
     */
    public static function remove($id)    
    {
    	$cache = FezCache::get();
    	$log = FezLog::get();
    	
    	if(! $cache) {
    		return false;    		
    	}
    	
    	try {
			return $cache->remove($id);				    	
		}
		catch (Exception $ex) {
			$log->debug($ex);
			return false;
		}		
    }
    
    /**
     * Test if a cache is available for the given id and (if yes) return it (false else)
     *
     * @param  string  $id                     Cache id
     * @param  boolean $doNotTestCacheValidity If set to true, the cache validity won't be tested
     * @param  boolean $doNotUnserialize       Do not serialize (even if automatic_serialization is true) => for internal use
     * @return mixed|false Cached datas
     */
    public static function load($id, $doNotTestCacheValidity = false, $doNotUnserialize = false)
    {
    	$cache = FezCache::get();
    	$log = FezLog::get();
    	
    	if(! $cache) {
    		return false;    		
    	}
    	
    	try {
			return $cache->load($id, $doNotTestCacheValidity, $doNotUnserialize);				    	
		}
		catch (Exception $ex) {
			$log->debug($ex);
			return false;
		}	
    }
    
    /**
     * Save some data in a cache
     *
     * @param  mixed $data           Data to put in cache (can be another type than string if automatic_serialization is on)
     * @param  string $id             Cache id (if not set, the last cache id will be used)
     * @param  array $tags           Cache tags
     * @param  int $specificLifetime If != false, set a specific lifetime for this cache record (null => infinite lifetime)
     * @param  int   $priority         integer between 0 (very low priority) and 10 (maximum priority) used by some particular backends         
     * @throws Zend_Cache_Exception
     * @return boolean True if no problem
     */
	public static function save($data, $id = null, $tags = array(), $specificLifetime = false, $priority = 8) 
	{
    	$cache = FezCache::get();
    	$log = FezLog::get();
    	
		if(! $cache) {
    		return false;    		
    	}
    	
    	try {
			return $cache->save($data, $id, $tags, $specificLifetime, $priority);				    	
		}
		catch (Exception $ex) {
			$log->debug($ex);
			return false;
		}		
    }
}
