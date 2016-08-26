<?php

/**
 * Interface FedoraApiInterface
 *
 * Interface for the backend repository API
 */
interface FedoraApiInterface
{
    /**
     * Gets the next available persistent identifier.
     *
     * @return string $pid The next available PID in from the PID handler
     */
    public static function getNextPID();

    /**
     * Gets the XML of a given object by PID.
     *
     * @param string $pid The persistent identifier
     * @return string $result The XML of the object
     */
    public static function getObjectXML($pid);

    /**
     * Gets the audit trail for an object.
     *
     * @param string $pid The persistent identifier
     * @return array of audit trail
     */
    public static function getAuditTrail($pid);

    /**
     * This function ingests a FOXML object and base64 encodes it
     *
     * @param string $foxml The XML object itself in FOXML format
     * @param string $pid The persistent identifier
     * @return bool
     */
    public static function callIngestObject($foxml, $pid = "");

    /**
     * Exports an associative array
     *
     * @param string $pid
     * @param string $format
     * @param string $context
     * @return array
     */
    public static function export($pid, $format = "info:fedora/fedora-system:FOXML-1.0", $context = "migrate");

    /**
     * Returns an associative array
     *
     * @param array $resultFields
     * @param int $maxResults
     * @param string $query_terms
     * @return array
     */
    public static function callFindObjects($resultFields = array(
      'pid',
      'title',
      'identifier',
      'description',
      'state'
    ), $maxResults = 10, $query_terms = "");

    /**
     * Resumes a find
     *
     * @param string $token
     * @return array
     */
    public static function callResumeFindObjects($token);

    /**
     * This function uses Fedora's simple search service which only really works against Dublin Core records.
     * @param string $query The query by which the search will be carried out.
     *		See http://www.fedora.info/wiki/index.php/API-A-Lite_findObjects#Parameters: for
     *		documentation of the syntax of the query.
     * @param array $fields The list of DC and Fedora basic fields to search against.
     * @return array $resultList The search results.
     */
    // Deprecate this function and replace calls to it
    //public function searchQuery($query, $fields = array('pid', 'title'));

    /**
     * This function removes an object and all its datastreams from Fedora
     *
     * @param string $pid The persistent identifier of the object to be purged
     * @return bool
     */
    public static function callPurgeObject($pid);

    /**
     * This function uses curl to upload a file into the fedora upload manager and calls the addDatastream or modifyDatastream as needed.
     *
     * @param string $pid The persistent identifier of the object to be purged
     * @param string $dsIDName The datastream name
     * @param string $file The file name
     * @param string $dsLabel The datastream label
     * @param string $mimetype The mimetype of the datastream
     * @param string $controlGroup The control group of the datastream
     * @param string $dsID The ID of the datastream
     * @param bool|string $versionable Whether to version control this datastream or not
     * @return integer
     */
    public static function getUploadLocation($pid, $dsIDName, $file, $dsLabel, $mimetype = 'text/xml', $controlGroup = 'M', $dsID = NULL, $versionable = FALSE);

    /**
     * This function uses curl to get a file from a local file location and upload it into the fedora upload manager and calls the addDatastream or modifyDatastream as needed.
     *
     * Developer Note: Mainly used by batch import of a SAN directory
     *
     * @param string $pid The persistent identifier of the object to be purged
     * @param string $dsIDName The datastream name
     * @param string $local_file_location The location of the file on a local server directory
     * @param string $dsLabel The datastream label
     * @param string $mimetype The mimetype of the datastream
     * @param string $controlGroup The control group of the datastream
     * @param string $dsID The ID of the datastream
     * @param bool|string $versionable Whether to version control this datastream or not
     * @return integer
     */
    public static function getUploadLocationByLocalRef($pid, $dsIDName, $local_file_location, $dsLabel, $mimetype, $controlGroup = 'M', $dsID = NULL, $versionable = FALSE);

    /**
     * This function adds datastreams to object $pid.
     *
     * @param string $pid The persistent identifier of the object to be purged
     * @param string $dsID The ID of the datastream
     * @param string $dsLocation The location of the file to add
     * @param string $dsLabel The datastream label
     * @param string $dsState The datastream state
     * @param string $mimetype The mimetype of the datastream
     * @param string $controlGroup The control group of the datastream
     * @param bool|string $versionable Whether to version control this datastream or not
     * @param string $xmlContent If it an X based xml content file then it uses a var rather than a file location
     * @param int $current_tries A counter of how many times this function has retried the addition of a datastream
     * @return void
     */
    public static function callAddDatastream($pid, $dsID, $dsLocation, $dsLabel, $dsState, $mimetype, $controlGroup = 'M', $versionable = FALSE, $xmlContent = "", $current_tries = 0);

    /**
     *This function creates an array of all the datastreams for a specific object.
     *
     * @param string $pid The persistent identifier of the object
     * @param string $createdDT Fedora timestamp of version to retrieve
     * @param string $dsState The datastream state
     * @return array $dsIDListArray The list of datastreams in an array.
     */
    public static function callGetDatastreams($pid, $createdDT = NULL, $dsState = 'A');

    /**
     *This function creates an array of all the datastreams for a specific object using the API-A-LITE rather than soap
     *
     * @param string $pid The persistent identifier of the object
     * @param bool $refresh Avoid a cached copy
     * @param int $current_tries A counter of how many times this function has retried the addition of a datastream
     * @return array $dsIDListArray The list of datastreams in an array.
     */
    public static function callListDatastreamsLite($pid, $refresh = FALSE, $current_tries = 0);

    /**
     * @param string $pid The persistent identifier of the object
     * @param bool $refresh
     * @return bool
     */
    public static function objectExists($pid, $refresh = FALSE);

    /**
     * This function creates an array of a specific datastream of a specific object
     *
     * @param string $pid The persistent identifier of the object
     * @param string $dsID The ID of the datastream
     * @param string $createdDT Date time stamp as a string
     * @return array The requested of datastream in an array.
     */
    public static function callGetDatastream($pid, $dsID, $createdDT = NULL);

    /**
     * Gets the history of a datastream.
     *
     * @param string $dsID The ID of the datastream
     * @return array of the history
     */
    public static function callGetDatastreamHistory($dsID);

    /**
     * Does a datastream with a given ID already exist in an object
     *
     * @param string $pid The persistent identifier of the object
     * @param string $dsID The ID of the datastream to be checked
     * @param bool $refresh Avoid a cached copy
     * @param bool $pattern a regex pattern to search against if given instead of ==/equivalence
     * @return boolean
     */
    public static function datastreamExists($pid, $dsID, $refresh = FALSE, $pattern = FALSE);

    /**
     * Does a datastream with a given ID already exist in existing list array of datastreams
     *
     * @param string $existing_list The existing list of datastreams
     * @param string $dsID The ID of the datastream to be checked
     * @return boolean
     */
    public static function datastreamExistsInArray($existing_list, $dsID);

    /**
     * This function creates an array of a specific datastream of a specific object
     *
     * @param string $pid The persistent identifier of the object
     * @param string $dsID The ID of the datastream to be checked
     * @param string $asofDateTime Gets a specified version at a datetime stamp
     * @return array The datastream returned in an array
     */
    public static function callGetDatastreamDissemination($pid, $dsID, $asofDateTime = "");

    /**
     * This function creates an array of a specific datastream of a specific object
     *
     * @param string $pid The persistent identifier of the object
     * @param string $dsID The ID of the datastream
     * @param boolean $getraw Get as xml
     * @param string $filehandle
     * @param int $current_tries A counter of how many times this function has retried
     * @return array $resultlist The requested of datastream in an array.
     */
    public static function callGetDatastreamContents($pid, $dsID, $getraw = FALSE, $filehandle = NULL, $current_tries = 0);

    /**
     * This function creates an array of specific fields from a specific datastream of a specific object
     *
     * @param string $pid The persistent identifier of the object
     * @param string $dsID The ID of the datastream
     * @param array $returnfields
     * @param string $asOfDateTime Gets a specified version at a datetime stamp
     * @return array The requested of datastream in an array.
     */
    public static function callGetDatastreamContentsField($pid, $dsID, $returnfields, $asOfDateTime = "");

    /**
     * This function modifies inline xml datastreams (ByValue)
     *
     * @param string $pid The persistent identifier of the object
     * @param string $dsID The name of the datastream
     * @param string $state The datastream state
     * @param string $label The datastream label
     * @param string $dsContent The datastream content
     * @param string $mimetype The mimetype of the datastream
     * @param bool|string $versionable Whether to version control this datastream or not
     * @return void
     */
    public static function callModifyDatastreamByValue($pid, $dsID, $state, $label, $dsContent, $mimetype = 'text/xml', $versionable = 'inherit');

    /**
     * This function modifies non-in-line datastreams, either a chunk o'text, a url, or a file.
     *
     * @param string $pid The persistent identifier of the object
     * @param string $dsID The name of the datastream
     * @param string $dsLabel The datastream label
     * @param string $dsLocation The location of the datastream
     * @param string $mimetype The mimetype of the datastream
     * @param bool|string $versionable Whether to version control this datastream or not
     * @return void
     */
    public static function callModifyDatastreamByReference($pid, $dsID, $dsLabel, $dsLocation = NULL, $mimetype, $versionable = 'inherit');

    /**
     * Changes the state and/or label of the object.
     *
     * @param string $pid The pid of the object
     * @param string $state The new state, A, I or D. Null means leave unchanged
     * @param string $label The new label. Null means leave unchanged
     * @param string $logMessage A log message
     */
    public static function callModifyObject($pid, $state, $label, $logMessage = 'Deleted by Fez');

    /**
     * This function marks a datastream as deleted by setting the state.
     *
     * @param string $pid The persistent identifier of the object
     * @param string $dsID The ID of the datastream
     * @return bool
     */
    public static function deleteDatastream($pid, $dsID);

    /**
     * This function deletes a datastream
     *
     * @param string $pid The persistent identifier of the object to be purged
     * @param string $dsID The name of the datastream
     * @param string $startDT The start datetime of the purge
     * @param string $endDT The end datetime of the purge
     * @param string $logMessage
     * @param bool $force
     * @return bool
     */
    public static function callPurgeDatastream($pid, $dsID, $startDT = NULL, $endDT = NULL, $logMessage = "Purged Datastream from Fez", $force = FALSE);
}