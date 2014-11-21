<?php

include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."config.inc.php");
require_once(__DIR__ . '/class.record.php');
require_once(__DIR__ . '/class.datastream.php');

/**
 * Class that contains utilities for webservice API.
 *
 * Not intended to be instantiated.
 *
 * @author Daniel Bush <danb@catalyst-au.net>
 *
 */
class API
{

    /**
     * Render array-format data as xml.
     *
     * If array has keys, these are used as tag names and we recurse on values.
     * If array is indexed, each element is stored in an <item> tag by
     * default.
     *
     * @param array $node The array you are converting to xml
     * @param function $indexFormatFn Option function that customise rendering behaviour for indexed arrays
     *
     * If you pass $indexFormatFn, elements with numeric keys belong
     * to parent tag $tagname will be rendered according to the return
     * values viz
     *   list($iname, $skipParent) = $indexFormatFn($tagname, array $children);
     * $iname will be used to wrap items with numeric keys.  It
     * defaults to 'item'.
     * If $skipParent, then $tagname is suppressed.
     *
     * If you want proper xml response with a single root node, pass
     * an assoc array with a SINGLE KEY as the root tagname.
     */
    public static function toXml(array $node, $indexFormatFn=null, $level=0, $indexedName=null)
    {
        $indent = str_repeat('  ', $level);
        $indent1 = str_repeat('  ', $level+1);
        $str = "";
        foreach ($node as $tagname => $childnode) {
            $skipParent = false;
            $iname = null;
            if (is_array($childnode)) {
                if ($indexFormatFn) {
                    list($iname, $skipParent) = $indexFormatFn($tagname, $childnode);
                    //print_r(array('iname' => $iname, 'skipParent' => $skipParent));
                    $childnode = self::toXml($childnode, $indexFormatFn, $level+1, $iname);
                } else {
                    $childnode = self::toXml($childnode, $indexFormatFn, $level+1);
                }
            } else if (is_object($childnode)) {
                $childnode = self::toXml((array)$childnode, $indexFormatFn, $level+1);
            } else {
                $childnode = htmlspecialchars($childnode);
            }
            if (is_int($tagname)) {
                $name = $indexedName ? $indexedName : 'item';
                $str .= PHP_EOL . $indent1 . "<$name>" . $childnode . "</$name>";
            } else {
                if ($indexFormatFn && $skipParent) {
                    $str .= $childnode;
                } else {
                    $str .= PHP_EOL . $indent1 . "<$tagname>";
                    $str .= $childnode;
                    $str .= "</$tagname>";
                }
            }
        }
        $str .= PHP_EOL . $indent;
        return $str;
    }

    /**
     * Render array-format data as json.
     */
    public static function toJson(array $node)
    {
        return json_encode($node);
    }

    /**
     * Parse xml by converting string to SimpleXmlElement instance.
     *
     * @return array of (SimpleXmlElement $sxml, string $err).
     *
     * Usage:
     *   list($sxml, $err) = parseXml($xml);
     *   // Check if $sxml is null, and use err to report message.
     */
    public static function parseXml($xml)
    {
        $sxml = null;
        $err = null;
        try {
            $sxml = @new SimpleXmlElement($xml);
        } catch (Exception $e) {
            //throw new Exception("Invalid xml response.");
            $err = "Invalid xml response.";
            $sxml = null;
        }
        return array($sxml, $err);
    }

    /**
     * Render array-format data into xml or json.
     *
     */

    public static function render(array $data, $type = 'xml')
    {
        switch ($type) {
            case 'xml':
                return self::toXml($data);
                break;
            case 'json':
                return self::toJson($data);
                break;
            default:
                // TODO: generate a 500 error?
                throw new Exception("API render: unexpected format: '$type'");
                break;
        }
    }

    /**
     * Set http status code and render and send a message ($data).
     *
     * @param array $data Should be array format
     *
     * Up to the caller whether ot exit the php process or not.
     */
    public static function reply($httpcode, array $data, $type = 'xml')
    {
        //http_response_code($httpcode); // >= 5.4 :(
        header("X-PHP-Response-Code: $httpcode", true, $httpcode);
        echo self::render($data, $type);
        ob_flush();
        flush();
    }

    /**
     * Make a standard resonse in array-format.
     *
     * Use to create non-template driven repsonses such as error
     * messages.
     * Pass to API:;render to render to required format.
     *
     * Use for success and error messages.
     * @param array $return Use this to return any specific id's.
     */
    public static function makeResponse($status, $msg = "", array $return = null)
    {
        $data = array(
            'response' => array(
                'status' => $status
            )
        );
        if ($msg) {
            $data['response']['msg'] = $msg;
        }
        if ($return) {
            $data['response']['return'] = $return;
        }
        return $data;
    }


    /**
     * Tests the permission value is valid for a datastream as defined in class.datastream.php
     * @param string $permission_value The permission value representation
     */
    private static function isValidDatastreamPermission($permission_value)
    {
        return isset(Datastream::$file_options[$permission_value]);
    }


    /**
     * Check date format. The expectation is that there is a year, month and day element or in the case the date
     * is being reset no values at all or empty values encapsulated in year, month and day.
     *
     * @param SimpleXmlElement $node The date node we're checking
     */
    private static function isValidDateField(SimpleXmlElement $node)
    {
        // If the year/month/day is not set, just consider this an empty date for convenience sake.
        if (!isset($node->year) || !isset($node->month) || !isset($node->day)) {
            $val = (string)$node;
            if (!empty($val)) {
                // They've set a value in the date xml that isn't empty.
                return false;
            }
            return true;
        }
        // Year/Mon/Day are represented by two digits. This is just how the dropdown picker works in fez.
        // TODO: Fix this at the turn of the century
        if (!(strlen((string)$node->year) == 4 || strlen((string)$node->year) == 0)
            || !(strlen((string)$node->month) == 2 || strlen((string)$node->month) == 0)
            || !(strlen((string)$node->day) == 2 || strlen((string)$node->day) == 0)
        ) {
            return false;
        } else {
            return true;
        }
    }



    /**
     * Extract xsd display fields sent to us via an api client into a
     * format suitable for using with fez.
     *
     * Specifically, we want something like the _POST variable when
     * data is POSTed to enter_metadata.
     *
     * extractXsdmfFields expects a simplexml element that
     * contains 0 or more <xsd_display_field> tags containing xsdmf
     * fields in a <xsd_display_fields> tag.
     *
     * The $xsd_df parameter is the display field structure according to
     * the REQUEST xdis_id.
     *
     * $details can also be provided, which are basically the record details object
     * to reuse.
     *
     * eg
     *   $sxml = new SimpleXmlElement('<something><xsd_display_fields><xsd_display_field>...</something>');
     *
     * @param SimpleXmlElement $sxml The SimpleXMLElement object we are parsing
     * @param $xsd_df The display object we compare our SimpleXmlElement against
     * @param $details The details object represents our record details if available.
     *
     */
    private static function extractXsdmfFields(SimpleXmlElement $sxml, $xsd_df, $details = array())
    {
        // pay no attention to this sorcery. It is a convenience for accessing fields.
        $ids = array_map(function ($xsdmf_array) {
            return $xsdmf_array['xsdmf_id'];
        }, $xsd_df);
        $xsd_df_better = array_combine($ids, $xsd_df);

        // Get all the required fields.
        $required = array_filter($xsd_df_better, function ($array) {
            if ($array['xsdmf_required'] == 1) {
                return true;
            }
        });

        if (count($sxml->xsd_display_fields) < 1) {
            API::reply(400, API::makeResponse(400, "Malformed xml. All required display fields should be submitted."), APP_API);
            exit;
        }
        foreach ($sxml->xsd_display_fields->xsd_display_field as $f) {
            $xsdmf_id = (int)$f->xsdmf_id;

            if (!isset($f->xsdmf_id)) {
                self::reply(
                    400,
                    self::makeResponse(
                        400,
                        "You submitted an xsd_display_field without an xsdmf_id child element."),
                    APP_API);
                exit;
            }

            if (!isset($f->xsdmf_value)) {
                // Every value POSTed should have xsdmf_id and xsdmf_value
                self::reply(
                    400,
                    self::makeResponse(
                        400,
                        "child element: xsdmf_value is required for xsdmf_id '" .
                        $f->xsdmf_id . "'."),
                    APP_API);
                exit;
            }

            $element = $f->xsdmf_value;

            $fielddef = $xsd_df_better[$xsdmf_id];

            // Check the field isn't empty.
            $multi_val = (count($element) > 1);
            if (!$multi_val) {
                $val_str = (string)$element;
            }

            // Take note if this was required.
            if ($fielddef['xsdmf_required'] == 1) {
                if (!$element || (!$multi_val && empty($val_str))) {
                    API::reply(400, API::makeResponse(
                        400,
                        "Missing required field for xsdmf_id: {$xsdmf_id} ."), APP_API
                    );
                    exit;
                } else {
                    unset($required[$xsdmf_id]);
                }
            }
            if ($fielddef['xsdmf_multiple'] == 1
                || $fielddef['xsdmf_html_input'] == 'multiple'
                || $fielddef['xsdmf_html_input'] == 'contvocab_selector') {
                $arr = array();
                foreach ($element as $val) {
                    array_push($arr, (string)$val);
                }
                $details[$xsdmf_id] = $arr;
            } elseif ($fielddef['xsdmf_html_input'] == 'date') {

                $elementStr = trim((string)$element);

                // Extract date as array("Day" => 01, "Month" => 03, "Year" => 2007).
                // 
                // xsdmf_date_type:
                // 1 => YYYY
                // 0 => YYYY-MM-DD
                // 
                // <xsdmf_value>
                //     <year>2014</year>
                //     [<month>10</month>]
                //     [<day>31</day>]
                // </xsdmf_value>

                if ($fielddef['xsdmf_date_type'] == 1) {
                    if (isset($element->year) ) {
                        $year = (string)$element->year;
                        $yearlength = strlen($year);
                        if ($yearlength == 4) {
                            $details[$xsdmf_id] = array('Year' => $year);
                        }
                        elseif ($yearlength == 0) {
                            // Ignore, blank date.
                        }
                        else {
                            API::reply(400, API::makeResponse(
                                400,
                                "Invalid date format for xsdmf_id: $xsdmf_id . " .
                                "Got '$year', expected 4 digits." ), APP_API
                            );
                        }
                    }
                    elseif (strlen($elementStr) > 0) {
                        API::reply(400, API::makeResponse(
                            400,
                            "Invalid date format for xsdmf_id: $xsdmf_id . " .
                            "Got '$elementStr', expected: <year>YYYY</year>" ), APP_API
                        );
                        exit;
                    }
                    else {
                        // Ignore, blank date.
                    }
                }

                // Anything else is assumed to be a Day/Month/Year tags.

                else {
                    if (API::isValidDateField($element)) {
                        $details[$xsdmf_id] = array(
                            'Year' =>  (string)$element->year,
                            'Month' => (string)$element->month,
                            'Day' => (string)$element->day
                        );
                    }
                    elseif (strlen($elementStr) > 0) {
                        API::reply(400, API::makeResponse(
                            400,
                            "Invalid date format.  Expected year/month/day tags, got: '$elementStr' . " .
                            "Please specify in the correct format."), APP_API
                        );
                        exit;
                    }
                    else {
                        // Ignore, blank date.
                    }
                }

            } elseif ($fielddef['xsdmf_html_input'] == 'checkbox') {
                if ($val_str === "off" || $val_str === 0) {
                    // Fez designates unchecking a checkbox by unsetting the xsdmf_id for the checkbox.
                    unset($details[$xsdmf_id]);
                } else {
                    $details[$xsdmf_id] = $val_str;
                }
            } else {
                if (isset($val_str)) {
                    $details[$xsdmf_id] = $val_str;
                } else {
                    // They're trying to set an array value to a field which is not a date or multiple input
                    API::reply(400, API::makeResponse(400, "Malformed xml."), APP_API);
                    exit;
                }
            }
            unset($val_str);
        }

        if (count($required) > 0) {
            // We haven't fulfilled all required fields!
            // We'll leave the session lingering in the case they want to resubmit.
            API::reply(400, API::makeResponse(400, "Required fields are not fulfilled."), APP_API);
            exit;
        }

        // Unset any other checkboxes which are 'off' or '0'
        $details = array_filter($details, function ($detail) {
            if ($detail === "off") {
                return false;
            } else {
                return true;
            }
        });

        // post expects $_POST['id'] = 'value'
        return $details;
    }

    /**
     * Extract the internal notes string from a SimpleXmlElement. The expectation is that internal notes
     * sits at the first depth of the SimpleXmlElement returned.
     *
     * @return string
     */
    private function extractInternalNotes(SimpleXmlElement $sxml)
    {
        return (string)$sxml->internal_notes;
    }


    /**
     * Extract the edit reason string from a SimpleXmlElement. The expectation is that edit reason
     * sits at the first depth of the SimpleXmlElement returned.
     *
     * @return string
     */
    private function extractEditReason(SimpleXmlElement $sxml)
    {
        return (string)$sxml->edit_reason;
    }


    /**
     * This function populates the global $_POST variable with the file_get_contents of the Request if
     * the Request is for an API resource (json/xml). This method is used specifically for when an email_body
     * is POSTed back for a rejection finalisation workflow step.
     */
    public function populateRejectionEmail()
    {
        $sxml = API::POSTcheckAPI();
        if ($sxml->reject->email_body) {

            // The get parameters
            $wfses_id = $_REQUEST['id'];
            $workflow_button_id = $_REQUEST['workflow'];
            $workflow_button_val = $_REQUEST['workflow_val'];

            $_POST['id'] = $wfses_id;
            $_POST['workflow_button_' . $workflow_button_id] = $workflow_button_val;

            $_REQUEST['email_body'] = (string)$sxml->reject->email_body;
        } else {
            API::reply(400, API::makeResponse(400, "A email_body is expected to finalise a rejection."), APP_API);
        }
    }


    /**
     * Extract the datastream details including id, description, embargo_date and permissions
     * associated with the file uploads performed.
     * Returns an array of these details.
     *
     * @return array
     */
    private function extractDatastreamCreateDetails(SimpleXmlElement $sxml)
    {
        $datastream = $sxml->datastreams->datastream_create;
        if (count($datastream) < 1) {
            return;
        }

        $xsdmf_id = (int)$datastream->xsdmf_id;
        if (!isset($xsdmf_id) || $xsdmf_id == 0) {
            // We need the xsdmf_id of the file uploader field to process this attachment.
            API::reply(400, API::makeResponse(400, "The xsdmf_id of the File_uploader field is required."), APP_API);
            exit;
        }

        // Datastream details are always represented as an array, even if it is one value
        $arr_description = array();
        $arr_permission = array();
        $arr_embargo = array();
        foreach ($datastream as $d) {
            array_push($arr_description, (string)$d->description);

            // So embargo dates are expected to be in the format dd-mm-yy on the POST.
            // We expect dates to come in a xml format <year>2014</year><month>12</month><day>30</day>
            if (API::isValidDateField($d->embargo_date)) {
                array_push(
                    $arr_embargo,
                    (substr((string)$d->embargo_date->year, -2) . '-' .
                    (string)$d->embargo_date->month . '-' .
                    (string)$d->embargo_date->day)
                );
            } elseif (isset($d->embargo_date)) {
                // They're trying to set an embargo date in the incorrect format. Correct them.
                API::reply(400, API::makeResponse(400, "Invalid embargo date format. Please specify in <embargo_date><year>XXXX</year><month>XX</month><day>XX</day></embargo_date> format."), APP_API);
                exit;
            }

            $p = (string)$d->permission;
            if (empty($p) || !API::isValidDatastreamPermission($p)) {
                API::reply(400, API::makeResponse(400, "Required datastream permissions are not fulfilled."), APP_API);
                exit;
            }
            array_push($arr_permission, $p);
        }
        // make sure the neccessary permissions are provided. This is a required field for attachment purposes.
        if (count($arr_permission) != count($datastream)) {
            API::reply(400, API::makeResponse(400, "Required datastream permissions are not fulfilled."), APP_API);
            exit;
        }

        return array($xsdmf_id, $arr_description, $arr_permission, $arr_embargo);
    }


    /**
     * Similar to @extractDatastreamCreateDetails but looks specifically at edit datastream information,
     * which should include details on the old file name (id) and the new file name. Also we do not expect
     * permission to be included as you would when editing a record. We just require that permission isn't
     * written to a value not possible. Below is a mapping of the xml fields to the $_POST variable.
     *
     *    <datastream_edit>
     *       <id>File.txt</id>                              //Filename_old & editedFilenames['originalFilename']
     *       <new_id>File_newname.txt</new_id>              //               editedFilenames['newFilename']
     *       <description>Some description</description>    //editedFileDescriptions
     *       <embargo_date></embargo_date>                  //embargoDateOld
     *       <permission>1</permission>                     //filePermissionsOld
     *   </datastream_edit>
     *
     * @return array The datastream edit details to be mapped to $_POST
     */
    private function extractDatastreamEditDetails(SimpleXMLElement $sxml)
    {
        $datastream = $sxml->datastreams->datastream_edit;
        if (count($datastream) < 1) {
            return;
        }

        // Datastream details are always represented as an array, even if it is one value
        $arr_filename_old = array();
        $arr_filename_new = array();
        $arr_description = array();
        $arr_permission = array();
        $arr_embargo = array();
        foreach ($datastream as $d) {

            if (!isset($d->id)) {
                // We need the filename to identify the change.
                API::reply(400, API::makeResponse(400, "The file id is required to identify the datastream to edit."), APP_API);
                exit;
            }

            if (!isset($d->new_id) && !isset($d->description) && !(isset($d->embargo_date) && isset($d->permission))) {
                // We need at least one other field we're changing.
                API::reply(400, API::makeResponse(400, "Require at least one datastream_edit attribute to change."), APP_API);
                exit;
            }

            // If you're changing the permissions or embargo date, you must include the embargo date, permission and filename
            // we've already checked the filename is included at this point
            if ((isset($d->embargo_date) && !isset($d->permission))  || (!isset($d->embargo_date) && isset($d->permission))) {
                API::reply(400, API::makeResponse(400, "Changing datastream permission or embargo date require you to specify permissions and embargo date."), APP_API);
                exit;
            }

            array_push($arr_filename_old, (string)$d->id);
            array_push($arr_filename_new, (string)$d->new_id);
            array_push($arr_description, (string)$d->description);

            // So embargo dates are expected to be in the format yyyy-mm-dd on the POST.
            // We expect dates to come in a xml format <year>2014</year><month>12</month><day>30</day>
            if (API::isValidDateField($d->embargo_date)) {
                array_push(
                    $arr_embargo,
                    (substr((string)$d->embargo_date->year, -2) . '-' .
                    (string)$d->embargo_date->month . '-' .
                    (string)$d->embargo_date->day)
                );
            } elseif (isset($d->embargo_date)) {
                // They're trying to set an embargo date in the incorrect format. Correct them.
                API::reply(400, API::makeResponse(400, "Invalid embargo date format. Please specify in <embargo_date><year>XXXX</year><month>XX</month><day>XX</day></embargo_date> format."), APP_API);
                exit;
            }
            if (isset($d->permission)) {
                $p = (string)$d->permission;
                if (empty($p) || !API::isValidDatastreamPermission($p)) {
                    API::reply(400, API::makeResponse(400, "Required datastream permissions are not fulfilled."), APP_API);
                    exit;
                }
                array_push($arr_permission, $p);
            }
        }

        // For reference these are the post variables that will be mapped to later.
        // $_POST['fileNamesOld']  && $_POST['editedFilenames']['originalFilename'] xxx
        // $_POST['editedFilenames']['newFilename']; xxx
        // $_POST['editedFileDescriptions']; xxxx
        // $_POST['filePermissionsOld']; xxx
        // $_POST['embargoDateOld']; xxxx

        return array($arr_filename_old, $arr_filename_new, $arr_description, $arr_permission, $arr_embargo);
    }


    /**
     * Checks the raw content POSTed. Looking for API format and validates that format; exits and returns response if not.
     */
    public function POSTcheckAPI()
    {
        $raw_cont = file_get_contents('php://input');
        if (APP_API == 'xml') {
            $xml = $raw_cont;
        } elseif (APP_API == 'json') {
            $raw_cont_arr = json_decode($raw_cont, true);
            $xml = new SimpleXMLElement('<workflow/>');
            array_walk_recursive($raw_cont_arr, array ($xml, 'addChild'));
        } else {
            API::reply(401, self::makeResponse(401, "Unrecognised format."), APP_API);
            exit;
        }

        list($sxml, $err) = self::parseXML($xml);
        if (is_null($sxml)) {
            // Use $err and send back error.
            API::reply(500, self::makeResponse(500, "Could not parse xml request."), APP_API);
            exit;
        }
        return $sxml;
    }


    /**
     * This function populates the global $_POST variable with the file_get_contents of the Request if
     * the Request is for an API resource (json/xml).
     * @param $xsd_df The display field object to check this request against. Typically called by getting the display details of a record
     * @param $xsd_df The display details. Typically these are the details already associated with this xsd_df (in the case of an edit)
     * @param $populate_record_context In some cases we may just be populating a form that is using xsd display fields but is not a record (ex. edit security metadata fields)
     * @return void
     **/
    public function populateThePOST($xsd_df = null, $details = null, $populate_record_context = true)
    {
        $sxml = API::POSTcheckAPI();
        // Check we have appropriate ids...
        $wfses_id = $_REQUEST['id'];
        $xdis_id = $_REQUEST['xdis_id'];
        $sta_id = $_REQUEST['sta_id'];
        $workflow_button_id = $_REQUEST['workflow'];
        $workflow_button_val = $_REQUEST['workflow_val'];

        if ($wfses_id === 0 || $xdis_id === 0 || $sta_id === 0) {
            self::reply(401, self::makeResponse(401, "Invalid wfses_id / sta_id / xdis_id."), APP_API);
            exit;
        }

        // If a record exists, this is an edit. If it doesn't exist, we're making a new record.
        if ($details) {
            //$xsd_df = $record->display->getMatchFieldsList(array("FezACML"), array());
            //$details = $record->getDetails();
            $xsd_display_fields = self::extractXsdmfFields($sxml, $xsd_df, $details);
        } else {
            if (!isset($xsd_df)) {
                $xsd_d = new XSD_DisplayObject($xdis_id);
                $xsd_df = $xsd_d->getMatchFieldsList(array("FezACML"), array());
            }
            $xsd_display_fields = self::extractXsdmfFields($sxml, $xsd_df);
            $_POST['cat'] = "report"; // It's expecting this report value from the html POST version of this. We'll add it in here to be in line.
        }

        $_POST['id'] = $wfses_id;
        $_POST['xdis_id'] = $xdis_id;
        $_POST['sta_id'] = $sta_id;
        $_POST['workflow_button_' . $workflow_button_id] = $workflow_button_val;
        $_POST['xsd_display_fields'] = &$xsd_display_fields;

        // This stuff doesn't need to be called if we're calling it as edit security context
        if ($populate_record_context) {
            $_POST['internal_notes'] = self::extractInternalNotes($sxml);
            $_POST['edit_reason'] = self::extractEditReason($sxml);
            $ds_details_create = self::extractDatastreamCreateDetails($sxml);
            if ($ds_details_create) {
                $_POST['uploader_files_uploaded'] = $ds_details_create[0];
                $_POST['description'] = $ds_details_create[1];
                $_POST['filePermissionsNew'] = $ds_details_create[2];
                $_POST['embargo_date'] =  $ds_details_create[3];
            }

            // extract datastream edit details will also populate with the missing values
            $ds_details_edit = self::extractDatastreamEditDetails($sxml);

            if ($ds_details_edit) {
                // Only set updated filename for those that specify a new filename and old filename set.
                for ($i=0; $i < count($ds_details_edit[0]); $i++) {
                    if ($ds_details_edit[0][$i] && $ds_details_edit[1][$i]) {
                        $arr = array();
                        $arr['originalFilename'] = $ds_details_edit[0][$i];
                        $arr['newFilename'] = $ds_details_edit[1][$i];
                        // pid is also normally set, but we have modified the edit_metadata page to not rely on the POST pid, but the one fetched from the workflow status
                        $_POST['editedFilenames'][] =  $arr;
                    }
                }

                // Only set new description details for those that have filename and a newlabel set.
                for ($i=0; $i < count($ds_details_edit[2]); $i++) {
                    if ($ds_details_edit[0][$i] && $ds_details_edit[2][$i]) {
                        $arr = array();
                        $arr['filename'] = $ds_details_edit[0][$i];
                        $arr['newLabel'] = $ds_details_edit[2][$i];
                        $_POST['editedFileDescriptions'][] = $arr;
                    }
                }

                // Only set those that have permission and embargo date set. This is checked already in extractDatastreamEditDetails.
                if ($ds_details_edit[3] && $ds_details_edit[4]) {
                    $_POST['fileNamesOld'] = $ds_details_edit[0];
                    $_POST['filePermissionsOld'] = $ds_details_edit[3];
                    $_POST['embargoDateOld'] = $ds_details_edit[4];
                }

            }
        }
    }
}
