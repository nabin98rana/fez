<?php
include_once(APP_INC_PATH . "class.fedora_direct_access.php");

define("DATASTREAM_DESCRIPTION", "Relationships between datastreams");

/**
 * Maintains the RELS-INT datastream of records.
 *
 * This datatastream is used to capture relationships between master datstreams and derived
 * datastreams.
 *
 * @author Matt Painter <matthew.painter@archives.govt.nz>
 */
class RelsInt {

	/**
	 * Returns the next available datastream identifier
	 *
	 * Datastream identifiers for file attachments are assumed to be in the format
	 * Prefix1, Prefix2, ... PrefixN
	 *
	 * @param $prefix prefix of datastreams
	 * @param $existingIDs array of existing datastreams in the record
	 * @return next available attachment datastream ID
	 */
	function getNextAvailableID($prefix, $existingIDs) 
	{
		$index = 1;

		while (in_array($prefix.$index, $existingIDs)) {
			$index++;
		}

		return $prefix.$index;
	}

	/**
	 * Calls Fedora to return the RELS-INT datastream contents for a record
	 *
	 * @param $pid PID of record to retrieve datastream for
	 * @return RELS-INT datastream XML
	 */
	function getRelsIntDatastream($pid) 
	{

		// Silently return null if the RELS-INT datastream doesn't exist
		if (!Fedora_API::datastreamExists($pid, 'RELS-INT')) {
			return null;
		}

		// Return datastream contents from Fedora
		return Fedora_API::callGetDatastreamContents($pid, 'RELS-INT', true);
	}



	/**
	 * Returns the list of master datastream IDs for a RELS-INT datastream
	 *
	 * @param $datastreamXml RELS-INT XML of the record
	 * @return array of master datastream IDs
	 */
	function getDatastreamIDs($datastreamXml) 
	{

		if ($datastreamXml == null) {
			return array();
		}

		$doc = DOMDocument::loadXML($datastreamXml);
		$xpath = new DOMXPath($doc);

		$ids = array();

		// Run XPath query to retrieve all parent datastream identifiers
		$nodes = $xpath->query("/rdf:RDF/datastream");
		foreach ($nodes as $node) {
			$id = $node->getAttribute("id");
			$ids[$id] = $id;
		}

		return array_keys($ids);

	}

	/**
	 * Returns a flat array of the identifiers of derived datastreams of a record
	 *
	 * @param $datastreamXml RELS-INT datastream containing relationships
	 * @return array of derived datastream IDs
	 */
	function getRelatedIDs($datastreamXml) 
	{
		if ($datastreamXml == null) return array();

		$doc = DOMDocument::loadXML($datastreamXml);
		$xpath = new DOMXPath($doc);

		$ids = array();

		// Run XPath query to retrieve all datastream identifiers
		$nodes = $xpath->query("//datastream");
		foreach ($nodes as $node) {
			$id = $node->getAttribute("id");

			// Only use IDs that have a 'key' attribute (ie, are related datastreams)
			if ($node->getAttribute("key") != "") {
				$ids[$id] = $id;
			}
		}

		return array_keys($ids);
	}

	/**
	 * Return the master datastream ID for a given related ID
	 *
	 * @param $dsID related ID to retrieve master ID for
	 * @param $datastreamXml RELS-INT datastream
	 * @return master datastream identifier
	 */
	function getParentDatastreamID($dsID, $datastreamXml) 
	{
		$doc = DOMDocument::loadXML($datastreamXml);
		$xpath = new DOMXPath($doc);

		$nodes = $xpath->query("/rdf:RDF/datastream/datastream[@id='$dsID']");

		if ($nodes->length == 0) {
			throw new Exception("No datastreams with identifier $dsID exist");
		}

		// Return identifier of parent node
		return $nodes->item(0)->parentNode->getAttribute("id");
	}


	/**
	 * Returns an array of IDs of derived datastreams for a given master
	 * datastream identifier.
	 *
	 * @param $dsID master datastream identifier
	 * @param $datastreamXml RElS-INT datastream
	 * @return array of related datastream identifiers
	 */
	function getRelatedDatastreamIDs($dsID, $datastreamXml) 
	{
		$doc = DOMDocument::loadXML($datastreamXml);
		$xpath = new DOMXPath($doc);

		$ids = array();

		$nodes = $xpath->query("/rdf:RDF/datastream[@id='$dsID']/datastream");

		foreach ($nodes as $node) {
			$ids[] = $node->getAttribute("id");
		}

		return $ids;
	}

	/**
	 * Returns a single derived datastream ID for a given master datastream
	 * identifier and a key
	 *
	 * @param $dsID master datastream identifier
	 * @param key of identifier to retrieve
	 * @param $datastreamXml RElS-INT datastream
	 * @return related datastream identifier
	 */

	function getRelatedDatastreamID($dsID, $key, $datastreamXml) 
	{

		if ($datastreamXml == null) {
			return null;
		}

		$doc = DOMDocument::loadXML($datastreamXml);
		$xpath = new DOMXPath($doc);

		$nodes = $xpath->query("/rdf:RDF/datastream[@id='$dsID']/datastream[@key='$key']");
		if ($nodes->length == 0) {
			throw new Exception("Related datastream with key $key does not exist for datastream with identifier $dsID");
		}

		$node = $nodes->item(0);
		if ($node == null) {
			return null;
		}
		else {
			return $node->getAttribute("id");
		}
	}

	/**
	 * Constructs an empty RELS-INT datastream string
	 *
	 * @return empty RELS-INT datastream XML
	 */
	function buildRELSINT() 
	{
		return '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"/>';
	}


	/**
	 * Removes a relationship node from a RELS-INT datstream and updates
	 * Fedora.
	 *
	 * This should be called when a datastream is purged from a record.
	 *
	 * @param $pid pid of record containing datastream
	 * @param $dsID identifier of relationship to remove
	 * @param $datastreamXml RELS-INT XML
	 * @param updateFedora (optional) true if the datastream in Fedora should be updated, false otherwise.
	 *        Defaults to true.
	 * @return updated RELS-INT XML
	 */
	function removeRelationshipDatastream($pid, $dsID, $datastreamXml, $updateFedora = true) 
	{
		$doc = DOMDocument::loadXML($datastreamXml);
		$xpath = new DOMXPath($doc);

		$nodes = $xpath->query("/rdf:RDF/datastream[@id='$dsID']");
		$node = $nodes->item(0);

		// Remove the node if it exists
		if ($node != null) {
			$docNodes = $xpath->query("/rdf:RDF");
			$docNode = $docNodes->item(0);
			$docNode->removeChild($node);
			$updatedXml = $doc->saveXML();
				
			if ($updateFedora) {
				Fedora_API::callModifyDatastreamByValue($pid, "RELS-INT", "A", DATASTREAM_DESCRIPTION, $updatedXml, "text/xml", 'inherit');
			}
		}

		return $updatedXml;
	}


	/**
	 * Returns the filename of a datastream.
	 *
	 * @param $dsID identifier of datastream to retrieve filename for
	 * @param $datastreamXml RELS-INT XML
	 * @return filename of $dsID
	 */
	function getDatastreamFilename($dsID, $datastreamXml) 
	{
		if ($datastreamXml == null) {
			return;
		}

		$doc = DOMDocument::loadXML($datastreamXml);
		$xpath = new DOMXPath($doc);

		// Run XPath query to retrieve datastream with the given identifier
		$nodes = $xpath->query("//datastream[@id='$dsID']");
		$node = $nodes->item(0);

		if ($node == null) {
			throw new Exception("No datastreams with identifier $dsID exist");
		}

		return $node->getAttribute("filename");
	}

	/**
	 * Returns the identifier of a datastream based on its filename.
	 *
	 * Note that if there are multiple datastreams with the same filename, only the
	 * first will be returned.
	 *
	 * @param $filename filename of datatream
	 * @param $datastreamXml RELS-INT XML
	 * @return identifier of datastream with filename $filename
	 */

	function getDatastreamIDFromFilename($filename, $datastreamXml) 
	{
		$doc = DOMDocument::loadXML($datastreamXml);
		$xpath = new DOMXPath($doc);

		$nodes = $xpath->query("//datastream[@filename='$filename']");
		$node = $nodes->item(0);

		if ($node == null) {
			throw new Exception("No datastreams with filename $filename exist");
		}

		return $node->getAttribute("id");
	}

	/**
	 * Adds a new datastream reference to a new or existing RELS-INT datastream and updates Fedora.
	 *
	 * @param $pid pid of record
	 * @param $dsID identifier of datastream to add reference to
	 * @param $dsFilename filename of file contained within datastream
	 * @param $datastreamXml (optional) RELS-INT datastream, if exists. If this is not present, a new
	 *        RELS-INT datastream is created if required.
	 * @param updateFedora (optional) true if the datastream in Fedora should be updated, false otherwise.
	 *        Defaults to true.
	 * @return updated RELS-INT XML
	 */
	function addDatastream($pid, $dsID, $dsFilename, $datastreamXml = null, $updateFedora = true) 
	{
		if ($datastreamXml == null) {

			// Create or retrieve RELS-INT datastream if not provided
			if (!Fedora_API::datastreamExists($pid, 'RELS-INT')) {
				$datastreamXml = RelsInt::createDatastream($pid);
			}
			else {
				$datastreamXml = RelsInt::getRelsIntDatastream($pid);
			}
		}

		$doc = DOMDocument::loadXML($datastreamXml);
		$xpath = new DOMXPath($doc);


		// Run XPath query to retrieve datastream with id $dsID. If none is found,
		// create one.
		$nodes = $xpath->query("/rdf:RDF/datastream[@id='$dsID']");
		if ($nodes->length == 0) {
			$newNode = $doc->createElement("datastream");
			$newNode->setAttribute("id", $dsID);
			$newNode->setAttribute("filename", $dsFilename);

			$parentNode = $xpath->query("/rdf:RDF")->item(0);

			$parentNode->insertBefore($newNode);
		}

		$updatedXml = $doc->saveXML();	// TODO: check for error

		// Update Fedora
		if ($updateFedora) {
			Fedora_API::callModifyDatastreamByValue($pid, "RELS-INT", "A", DATASTREAM_DESCRIPTION, $updatedXml, "text/xml", 'inherit');
		}
		return $updatedXml;
	}

	/**
	 * Adds derived relationships for a given master ID  and updates
	 * Fedora.
	 *
	 * This should be called when a datastream is created for a record.
	 *
	 * @param $pid pid of record containing datastream
	 * @param $dsID identifier of master datastream
	 * @param $dsID filename of file in master datastream
	 * @param $relatedKey key for related datastream ('web', 'preview', ecetera)
	 * @param $relatedFilename filename for file managed by related datastream
	 * @param $datastreamXml RELS-INT XML, if present
	 * @param updateFedora (optional) true if the datastream in Fedora should be updated, false otherwise.
	 *        Defaults to true.
	 * @return updated RELS-INT XML
	 */
	function addRelationshipForDatastream($pid, $dsID, $dsFilename, $relatedKey, $relatedID, $relatedFilename, $datastreamXml, $updateFedora = true) 
	{
		$doc = DOMDocument::loadXML($datastreamXml);
		$xpath = new DOMXPath($doc);

		$nodes = $xpath->query("/rdf:RDF/datastream[@id='$dsID']");
		if ($nodes->length == 0) {
			// TODO throw exception
			die();
		}

		$node = $nodes->item(0);	// Should only be one node
		$nodes = $xpath->query("/rdf:RDF/datastream[@id='$dsID']/datastream[@key='$relatedKey']");

		if ($nodes->length == 0) {
			$newNode = $doc->createElement("datastream");
			$newNode->setAttribute("id", $relatedID);
			$newNode->setAttribute("key", $relatedKey);
			$newNode->setAttribute("filename", $relatedFilename);

			$node->insertBefore($newNode);
		}
		else {
			// Node exists; modify
			$node = $nodes->item(0);
			$node->setAttribute("id", $relatedID);
			$newNode->setAttribute("filename", $relatedFilename);
		}


		$updatedXml = $doc->saveXML();	// TODO: check for error

		if ($updateFedora) {
			Fedora_API::callModifyDatastreamByValue($pid, "RELS-INT", "A", DATASTREAM_DESCRIPTION, $updatedXml, "text/xml", 'inherit');
		}
		return $updatedXml;
	}

	/**
	 * Creates a new RELS-INT datastream for a record and updates Fedora.
	 *
	 * @param $pid pid of record to add RELS-INT datastream for
	 */
	function createDatastream($pid) 
	{
		$relsInt = RelsInt::buildRELSINT();
		if (Fedora_API::datastreamExists($pid, "RELS-INT")) {
			Fedora_API::callModifyDatastreamByValue($pid, "RELS-INT", "A", DATASTREAM_DESCRIPTION, $relsInt, "text/xml", 'inherit');
		} else {
			Fedora_API::getUploadLocation($pid, "RELS-INT", $relsInt, DATASTREAM_DESCRIPTION, "text/xml", "X", null, 'true');
		}

		return $relsInt;
	}
}
