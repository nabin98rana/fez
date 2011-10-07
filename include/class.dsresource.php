<?php

class DSResource
{
    const HASH_DEPTH = 4;
    
    /**
     * array("rawHash"=> "string", 
     * "hashFile"=> "string", 
     * "hashPath"=> "string")
     * @var <array>
     */
    protected $hash = array();
    
    /**
     * Temp upload location
     * @var <string>
     */
    protected $tmpPath;
    
    /**
     * The root of the CAS
     * @var <string>
     */
    protected $dsTreePath;
    
    /**
     * array('mimetype' => 'string', 
     * 'controlgroup' => 'char', 
     * 'state' => 'char', 
     * 'size' => 'int',
     * 'pid' => 'string')
     * @var <array>
     */
    protected $meta = array();
    
    /**
     * Logging object.
     * @var <FezLog>
     */
    private $log;
    
    /**
     * The database object.
     * @var Zend_Db
     */
    private $db;
    
    /**
     * Set up the root of the CAS
     * @param <string> $dsTreePath
     * @param <string> $resourcePath
     * @param <array> $meta
     */
    public function __construct($dsTreePath=NULL, $resourcePath=NULL, $meta=NULL)
    {
        $this->dsTreePath = ($dsTreePath) ? $dsTreePath : APP_DSTREE_PATH;
        $this->log = FezLog::get();
        $this->db = DB_API::get();
        
        if($resourcePath)
        {
            $this->makeHash($resourcePath);
        }
        
        if($meta)
        {
            $this->meta = $meta;
        }
    }
    
    /**
     * Return the current hash array.
     */
    public function getHash()
    {
        return $this->hash;
    }
    
    /**
     * Return the current meta array
     */
    public function getMeta()
    {
        return $this->meta;
    }
    
    /**
     * Aquire an md5 hash of the resource and create the path
     * in the storage to the specified depth.
     * @param <string> $resourcePath
     */
    protected function makeHash($resourcePath)
    {
        $this->tmpPath = $resourcePath;
        
        $file = explode('/', $resourcePath);
        $file = $file[count($file)-1];
        $hash['rawHash'] = md5_file($resourcePath);
        
        $hash['hashFile'] = $file;
        $hash['hashPath'] = $this->createPath($hash['rawHash']);
        
        $this->hash = $hash;
    }
    
    /**
     * Create a path for the CAS based on the file hash
     * @param <string> $hash
     */
    public function createPath($hash)
    {
        $hashPath = array();
        
        for($i=0;$i<self::HASH_DEPTH;$i++)
        {
            $hashPath[] = substr($hash, $i*2, 2);
        }
        
        $hashPath = implode('/', $hashPath) . '/'; 
        
        return $hashPath;
    }
    
    /**
     * Populate the meta property
     * @param <array> $meta
     */
    public function setMeta($meta)
    {
        $this->meta = $meta;
    }
    
    /**
     * Load a resource's reference and metadata 
     * from the DB into the object at the head revision.
     * @param <string> $hash
     */
    public function load($filename, $pid)
    {
        $row = $this->getDSRev($filename, $pid);
        
        $this->hash['hashFile'] = $row['filename'];
        $this->hash['rawHash'] = $row['hash'];
        $this->hash['hashPath'] = $this->createPath($row['hash']);
        
        $this->meta['mimetype'] = $row['mimetype'];
        $this->meta['controlgroup'] = $row['controlgroup'];
        $this->meta['state'] = $row['state'];
        $this->meta['size'] = $row['size'];
        $this->meta['pid'] = $row['pid'];
    }
    
    /**
     * Check if the resource already exists in the CAS
     * @param <string> $hash
     */
    public function resourceExists($hash)
    {
        $resource = $this->dsTreePath . $this->createPath($hash) . $hash;
        
        try 
        {
            $sql = "SELECT hash FROM " . APP_TABLE_PREFIX . "file_attachments WHERE hash = :hash";
            $stmt = $this->db->query($sql, array(':hash' => $hash));
            $row = $stmt->fetch();
        }
        catch(Exception $e)
        {
            $this->log->err($e->getMessage());
        }
        return ($row['hash'] && is_file($resource)) ? true : false;
    }
    
    /**
     * Retrieve a specific version of a data stream (head by default).
     * This excludes any binary data from the CAS.
     * @param <string> $fileName
     * @param <string> $revision
     */
    public function getDSRev($fileName, $pid, $revision='HEAD')
    {
        try 
        {
            if($revision == 'HEAD')
            {
                //TODO Rework this query. No longer need MAX as the versions are in the shadow table.
                $sql = "SELECT id, metaid, hash, size, filename, mimetype, controlgroup, "
                . "pid, state, version FROM " . APP_TABLE_PREFIX . "file_attachments " 
                . "WHERE filename = :dsfilename "
                . "AND pid = :pid AND state = 'A' AND version = (SELECT MAX(VERSION) FROM " 
                . APP_TABLE_PREFIX . "file_attachments WHERE " 
                . "filename = :ifilename AND pid = :ipid)";
                
                $stmt = $this->db->query($sql, array(':dsfilename' => $fileName, ':pid' => $pid,
                                            ':ifilename' => $fileName, ':ipid' => $pid));
            }
            else 
            {
                $sql = "SELECT id, metaid, hash, size, filename, mimetype, controlgroup, pid, state, version FROM " 
                    . APP_TABLE_PREFIX . "file_attachments__shadow WHERE "
                    . "state = 'A' AND filename = :dsfilename "
                    . "AND version = :version AND pid = :pid";
                $stmt = $this->db->query($sql, array(':dsfilename' => $fileName, 
                	':version' => $revision, ':pid' => $pid));
            }
            
            $row = $stmt->fetch();
        }
        catch(Exception $e)
        {
            $this->log->err($e->getMessage());
        }
        
        return $row;
    }
    
    /**
     * Retrieve all versions of a file.
     * @param <string> $fileName
     */
    public function getDSRevs($fileName, $pid)
    {
        try
        {
            $sql = "SELECT id, hash, filename, pid, version FROM "  
                . APP_TABLE_PREFIX . "file_attachments WHERE " 
                . "filename = :dsfilename AND pid = :pid ORDER BY version DESC";
            $stmt = $this->db->query($sql, array(':dsfilename' => $fileName, ':pid' => $pid));
            $rows = $stmt->fetchAll();
        }
        catch(Exception $e)
        {
            $this->log->err($e->getMessage());
        }
        
        return $rows;
    }
    
    /**
     * Retrieve a count of all revisions of a given resource
     * @param <string> $filename
     * @param <string> $pid
     */
    public function getDSRevCount($filename, $pid)
    {
        try
        {
            $sql = "SELECT count(*) as count FROM " . APP_TABLE_PREFIX . "file_attachments "
            . "WHERE filename = :filename AND pid = :pid";
            $stmt = $this->db->query($sql, array(':filename' => $filename, ':pid' => $pid));
            $row = $stmt->fetch();
        }
        catch(Exception $e)
        {
            $this->log->err($e->getMessage());
        }
        
        return (isset($row['count'])) ? $row['count'] : false;
    }
    
    /**
     * List all the resources for a PID
     * @param <string> $pid
     */
    public function listStreams($pid, $distinct=true)
    {
        $rows = false;
        $distinct = ($distinct) ? ' DISTINCT' : '';
        
        try
        {
        $sql = "SELECT{$distinct} id, hash, filename, pid FROM "  . APP_TABLE_PREFIX 
            . "file_attachments WHERE " 
            . "pid = :pid GROUP BY filename";
        $stmt = $this->db->query($sql, array(':pid' => $pid));
        $rows = $stmt->fetchAll();
        }
        catch(Exception $e)
        {
            $this->log->err($e->getMessage());
        }
        
        return $rows;
    }
    
    /**
     * Retrieve a stream's data from the CAS
     * @param <string> $hash
     */
    public function getDSData($hash)
    {
        $dsPath = $this->dsTreePath . $this->createPath($hash) . $hash;
        if(is_file($dsPath))
        {
            $fileData = file_get_contents($dsPath);
            return $fileData;
        }
    }
    
    /**
     * Store the reference to a resource version in the database
     */
    protected function storeDSReference()
    {
        $now = Zend_Registry::get('version');
        
        try
        {
            //does a record with this file name and hash already exist?
            $sql = "SELECT hash FROM " . APP_TABLE_PREFIX . "file_attachments "
                . "WHERE hash = :dshash AND pid = :pid "
                . "AND version = :version";
            
            $stmt = $this->db->query($sql, array(
            	':dshash' => $this->hash['rawHash'], 
                ':version' => $now,
                ':pid' => $this->meta['pid']));
            $row = $stmt->fetch();
            
            if(!$row)
            {
                $sql = "INSERT INTO " . APP_TABLE_PREFIX . "file_attachments "
                    ."(hash, filename, version, pid, size, mimetype) VALUES "
                    ."(:dshash, :dsfilename, :version, :pid, :size, :mimetype)";
                    
                $this->db->query($sql, array(':dshash' => $this->hash['rawHash'], 
                	':dsfilename' => $this->hash['hashFile'],
                    ':size' => $this->meta['size'],
                    ':version' => $now,
                    ':mimetype' => $this->meta['mimetype'],
                    ':pid' => $this->meta['pid']));
            }
        }
        catch(Exception $e)
        {
            $this->log->err($e->getMessage());
        }
    }
    
    /**
     * Save the meta data for a given stream
     */
    protected function storeDSMeta($data)
    {
        //TODO - Can this method be absorbed into another method
        //now that we no longer have a seperate meta table?
        try
        {
            $sql = "SELECT metaid from " . APP_TABLE_PREFIX 
                . "file_attachments fa INNER JOIN " . APP_TABLE_PREFIX . "file_meta fm "
                . "ON fm.id = fa.metaid "
                ."WHERE filename = :filename AND pid = :pid";
            $stmt = $this->db->query($sql, array(':filename' => $this->hash['hashFile'], 
                                                ':pid' => $data['pid']));
            $row = $stmt->fetch();
            
            if($row && $row['metaid'] > 0)
            {
                $metaId = $row['metaid'];
            }
            else
            {
                $sql = "INSERT INTO " . APP_TABLE_PREFIX . "file_meta " 
                    . "(mimetype, controlgroup, state, pid) VALUES " 
                    . "(:mimetype, :controlgroup, :state, :pid)";
                $this->db->query($sql, array(':mimetype' => $data['mimetype'] , 
                	':controlgroup' => $data['controlgroup'], 
                	':state' => $data['state'], 
                	':pid' => $data['pid']));
                $metaId = $this->db->lastInsertId();
            }
            
            return $metaId;
        }
        catch(Exception $e)
        {
            $this->log->err($e->getMessage());
        }
    }
    
    /**
     * Create a directory based on the hash of the file and move the resource
     * from temp storage if the resource does not already exist.
     */
    public function save()
    {
        if(!$this->resourceExists($this->hash['rawHash']))
        {
            if(mkdir($this->dsTreePath . $this->hash['hashPath'], 0770, true))
            {
                if(copy($this->tmpPath, $this->dsTreePath . $this->hash['hashPath'] 
                    . $this->hash['rawHash']))
                {
                    $this->storeDSReference();
                    return true;
                }
            }
        }
        elseif($this->resourceExists($this->hash['rawHash']))
        {
            $this->storeDSReference();
            return true;
        }
        
        return false;
    }
    
    /**
     * Remove a resource and its directories.
     */
    protected function deleteResource($hash)
    {
        if($this->resourceExists($hash))
        {
            $hashPath = $this->createPath($hash);
            if(unlink($this->dsTreePath . $hashPath . $hash))
            {
                $rmdir = explode('/',$hashPath);
                //PHP's rmdir() does not recurse. Need to do this manually.
                while(count($rmdir))
                {
                    $currentDir = $this->dsTreePath . implode('/', $rmdir);
                    if(is_dir($currentDir))
                    {
                        rmdir($currentDir);
                    }
                    array_pop($rmdir);
                }
            }
        }
        
        return false;
    }
    
    /**
     * Mark a datastream for a particular PID
     * as deleted.
     */
    public function dereference()
    {
        $datastream = $this->getDSRev($this->hash['hashFile'], $this->meta['pid']);
        
        $sql = "UPDATE " . APP_TABLE_PREFIX . "file_attachments SET state = 'D' "
            . "WHERE version = :version AND filename = :filename AND pid = :pid";
            
        $this->db->query($sql, array(':version' => $datastream['version'],
                                    ':filename' => $datastream['filename'],
                                    ':pid' => $datastream['pid']));
    }
    
    /**
     * Remove resource references and its on-disk data.
     * Can remove all revisions or just a single revision.
     * $revs is a string or a MySQL timestamp.
     * @param <mixed> $revs
     */
    public function delete($revs='HEAD')
    {
        //If we're deleting all revs, we only need the data for the head revision.
        $revP = ($revs == 'ALL') ? 'HEAD' : $revs;
        $revData = $this->getDSRev($this->hash['hashFile'], $this->meta['pid'], $revP);
        $revCount = $this->getDSRevCount($this->hash['hashFile'], $this->meta['pid']);
        
        if($revs === 'ALL')
        {
            //These are what we need to delete off disk.
            $allRevs = $this->getDSRevs($this->hash['hashFile'], $revData['pid']);
            
            try
            {
                //Get rid of everything in the *_file_attachments table for this resource.
                $sql = "DELETE FROM " . APP_TABLE_PREFIX . "file_attachments WHERE "
                . "filename = :filename AND metaid = :metaid";
                $this->db->query($sql, array(':filename' => $this->hash['hashFile'], 
                							':metaid' => $revData['metaid']));
            }
            catch(Exception $e)
            {
                $this->log->err($e->getMessage());
            }
            
            //Get rid of the files off the disk one by one.
            foreach($allRevs as $rev)
            {
                $this->deleteResource($rev['hash']);
            }
        }
        else
        {
            //Get rid of only one rev.
            try
            {
                $sql = "DELETE FROM " . APP_TABLE_PREFIX . "file_attachments WHERE id = :id";
                $this->db->query($sql, array(':id' => $revData['id']));
            }
            catch(Exception $e)
            {
                $this->log->err($e->getMessage());
            }
            
            $this->deleteResource($revData['hash']);
        }
        
        //Get rid of the metadata for this resource if have deleted the last rev or if we are deleting all revs.
        /*if($revCount == 1 || $revs === 'ALL')
        {
            try
            {
                $sql = "DELETE FROM " . APP_TABLE_PREFIX . "file_meta WHERE id = :metaid";
                $this->db->query($sql, array(':metaid' => $revData['metaid']));
            }
            catch(Exception $e)
            {
                $this->log->err($e->getMessage());
            }
        }*/
    }
    
    /**
     * Rename a resource filename in the DB (all versions)
     * for a given pid
     * @param <string> $oldName
     * @param <string> $newName
     * @param <string> $pid
     */
    public function rename($oldName, $newName, $pid)
    {
        $res = false;
        if($this->getDSRev($oldName, $pid))
        {
            try
            {
                /*$sql = "UPDATE " . APP_TABLE_PREFIX . "file_attachments att, " . APP_TABLE_PREFIX 
                     . "file_meta met SET filename = :newFileName WHERE att.metaid = met.id AND "
                     . "att.filename = :oldFileName AND met.pid = :pid";*/
                $sql = "UPDATE " . APP_TABLE_PREFIX . "file_attachments SET filename = :newFileName WHERE "
                     . "filename = :oldFileName AND pid = :pid";
                $res = $this->db->query($sql, array(':newFileName' => $newName, 
             	    ':oldFileName' => $oldName, 
             	    ':pid' => $pid));
            }
            catch(Exception $e)
            {
                $this->log->err($e->getMessage());
            }
        }
        else 
        {
            return $res;
        }
    }
}