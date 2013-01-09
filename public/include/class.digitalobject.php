<?php

class DigitalObject
{
    /**
     * The object's DB connection.
     * @var <Zend_Db>
     */
    private $_db;

    /**
     * Metadata for a digital object.
     * @var <array>
     */
    private $_pidData;

    /**
     * Logging object.
     * @var <FezLog>
     */
    private $_log;

    /**
     * The currentn PID
     * @var <string>
     */
    private $_pid;

    /**
     * A DSResource to handle datastreams.
     * @var <DSResource>
     */
    private $_dsResource;

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
        $this->_db = DB_API::get();
        $this->_log = FezLog::get();
        $this->_dsResource = new DSResource();
    }

    /**
     * Insert or update a digital object
     * and generate a PID if required.
     * Updates if PID provided otherwise inserts.
     * @param <array> $objdata
     */
    public function save($objdata)
    {
        if (array_key_exists('pid', $objdata)) {
            $this->_pid = $objdata['pid'];

            list($pidns, $pidint) = explode(":", $objdata['pid']);

            unset($objdata['pid']); //Exclude the pid from the update.
            $updateFields = $this->setUpdateFields($objdata);

            try {
                $sql = "UPDATE " . APP_TABLE_PREFIX . "digital_object SET "
                    . $updateFields['set'] . " WHERE pidns = :pidns AND pidint = :pidint";

                //Bind pidint and pidns to the WHERE in the update.
                $updateFields['binding'][':pidns'] = $pidns;
                $updateFields['binding'][':pidint'] = $pidint;

                $this->_db->query($sql, $updateFields['binding']);
            } catch(Exception $e) {
                $this->_log->err($e->getMessage());
            }
        } else {
            //If no namespace, use the one in the config.
            $pidns = ($objdata['pidns']) ? $objdata['pidns'] : APP_PID_NAMESPACE;

            try {
                $sql = "SELECT MAX(pidint)+1 AS pidint FROM " . APP_TABLE_PREFIX
                    . "digital_object WHERE pidns = :pidns";
                $stmt = $this->_db->query($sql, array(':pidns' => $pidns));
                $pidint = $stmt->fetch();
            } catch(Exception $e) {
                $this->_log->err($e->getMessage());
            }

            //Check to see if this the first pid for this namespace.
            $pidint = ($pidint['pidint'] == NULL) ? 1 : $pidint['pidint'];
            $pidint = ($pidint < self::PID_NO_CLASH) ? self::PID_NO_CLASH : $pidint;

            $this->_pid = $pidns . ":" . $pidint;

            $objdata['pidint'] = $pidint;
            $objdata['pidns'] = $pidns;

            $insert = $this->setInsertFields($objdata);

            try {
                $sql = "INSERT INTO " . APP_TABLE_PREFIX . "digital_object " . $insert['insert'];
                $this->_db->query($sql, $insert['binding']);
            } catch(Exception $e) {
                $this->_log->err($e->getMessage());
            }
        }

        return $this->_pid;
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
        foreach ($fields as $fieldk => $fieldv) {
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

        foreach ($data as $datak => $datav) {
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
     * Return all data for a PID
     * @param <string> $pid
     */
    public function get($pid)
    {
        try {
            $sql = "SELECT * FROM " . APP_TABLE_PREFIX . "record_search_key WHERE"
                . " rek_pid = :pid";
            $stmt = $this->_db->query($sql, array(':pid' => $pid));
            return $stmt->fetch();
        } catch(Exception $e) {
            $this->_log->err($e->getMessage());
        }
    }

    /**
     * Load metadata into the object
     * @param <string> $pid
     */
    public function load($pid)
    {
        $pidData = $this->get($pid);
        $pidData['pid'] = $pid;
        $this->_pidData = $_pidData;
    }


    /**
     * Get all the datastreams for a PID
     * @param <array> $params
     */
    public function getDatastreams($params)
    {
        $dsList = $this->_dsResource->listStreams($params['pid']);
        $datastreams = array();

        $rev = (isset($params['rev'])) ? $params['rev'] : 'HEAD';

        foreach ($dsList as $datastream) {
            $dsFormatted = array();
            $dsRev = $this->_dsResource->getDSRev($datastream['filename'], $datastream['pid'], $rev);
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

            $datastreams[] = $dsFormatted;
        }

        return $datastreams;
    }

    //A record can be published but deleted. Which means the tombstone is viewable by the public.
    public function isPublished($pid)
    {
        $stmt = "SELECT rek_status FROM " . APP_TABLE_PREFIX . "record_search_key WHERE"
            . " rek_pid = ".$this->_db->quote($pid);

        try {
            $result = $this->_db->fetchOne($stmt);
            if (empty($result)) {
                $stmt = "SELECT rek_status FROM fez_record_search_key__shadow WHERE rek_pid = ".$this->_db->quote($pid)." ORDER BY rek_stamp DESC LIMIT 1";
                try {
                    $result = $this->_db->fetchOne($stmt);
                }
                catch(Exception $ex) {
                    $this->_log->err($ex);
                    return false;
                }
            }
        } catch(Exception $ex) {
            $this->_log->err($ex);
            return false;
        }
        return $result;

    }

}