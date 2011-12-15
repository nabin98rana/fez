<?php 
require_once 'Zend/Session/SaveHandler/Interface.php';
require_once 'class.auth.php';

class Fez_Session_Manager implements Zend_Session_SaveHandler_Interface
{
	/**
	 * This is instance of Session_Manager, which extends Zend_Db_Table and manages the database connection
	 *
	 * @var Session_Manager
	 */
	protected $sessionData;
 
	protected $thisIsOldSession = false;
	protected $originalSessionId = '';
 
	public function open($save_path, $name)
	{
		$this->sessionData = new Fez_Session_Data();
		return true;
	}
 
	public function close()
	{
		return true;
	}
 
	public function read($id)
	{
		$rows = $this->sessionData->find($id);
		$row = $rows->current();
		if ($row)
		{
			$this->thisIsOldSession = true;
			$this->originalSessionId = $id;
			return $row->session_data;
		}
		else
		{
			return '';
		}
	}
        
        /**
         * Insert new / update existing session data on the designated sessions db table.
         * 
         * @param String $id Session ID
         * @param String $sessionData Session Data
         * @return Boolean  
         */
	public function write($id, $sessionData)
	{
                // Prepare the value of User ID 
		$userID = Auth::getUserID();
		if ($userID == '') {
			$userID = null;
		}

                // Do nothing when its session data AND user ID values are empty/null, 
                // as the record becomes useless and may create clogging on database server. 
                // In theory, there should be value on $sessionData when user is logged in, 
                // we apply condition checking on both variable just to avoid distruption on session data for logged in users. 
                if (empty($sessionData) && (empty($userID) || is_null($userID)) ){
                    return;     // Adios sayonara
                }
                
		$data = array
		(
			'session_data' => $sessionData,
			'session_ip' => $_SERVER['REMOTE_ADDR'],
			'updated' => date('Y-m-d H:i:s'),
			'user_id' => $userID
		);

                // session ID is regenerated, so set $thisIsOldSession to false, so we insert new row
		if ($this->thisIsOldSession && $this->originalSessionId != $id)
		{
			$this->thisIsOldSession = false;
		}
 
                // Update existing session record
		if ($this->thisIsOldSession)
		{
			$this->sessionData->update
			(
				$data,
				$this->sessionData->getAdapter()->quoteInto('session_id = ?', $id)
			);
		}
                // Insert new session record
		else
		{
                        //no such session, create new one
			$data['session_id'] = $id;
			$data['created'] = date('Y-m-d H:i:s');
			
			$this->sessionData->insert($data);
		}
 
		return true;
	}

	public function __destruct() 
	{
		// set the profiler to disabled, otherwise we get an issue when trying to write to a profiled database connection after the object has been destructed
		$this->sessionData->getAdapter()->getProfiler()->setEnabled(false);
	}

	public function destroy($id)
	{
		$this->sessionData->delete($this->sessionData->getAdapter()->quoteInto('session_id = ?', $id));
		return true;
	}
 
	public function gc($maxLifetime)
	{
		$maxLifetime = intval($maxLifetime);
		if (!is_numeric(strpos(APP_SQL_DBTYPE, "mysql"))) { //eg if postgresql etc
			$this->sessionData->delete("updated + INTERVAL '$maxLifetime seconds' < NOW()");
		} else {
			$this->sessionData->delete("DATE_ADD(updated, INTERVAL $maxLifetime SECOND) < NOW()");
		}
		return true;
	}
}