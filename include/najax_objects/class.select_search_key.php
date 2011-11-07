<?php

/**
 * SelectSearchKey
 * This class maps into the javascript through NAJAX.  The javascript in the template populates the select boxes 
 * using calls to this class through NAJAX.
 */
class SelectSearchKey {

    public $tpl = null;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->tpl = new Template_API();
        $this->tpl->setTemplate("workflow/select_search_key_values.tpl.html");
        $this->tpl->assign('rel_url', APP_RELATIVE_URL);
        $this->tpl->assign('input_name', 'sek_value');
        $this->tpl->assign('field_type', "select_single");
    }


    /**
     * Returns array of value & text for the search key replacement value drop down.
     * @param $sek_id
     * @return array|string
     */
    function getSearchKeyOptions($sek_id)
    {
        $list_field = Search_Key::getDetails($sek_id);
        $output = array();
        $result = array();

        if ($list_field['sek_html_input'] == 'contvocab' && $list_field['sek_cardinality'] != 1) {
            $cv = new Controlled_Vocab();
            $result = $cv->getAssocListFullDisplay($list_field['sek_cvo_id']);
        }

        // Date input type
        if ($list_field['xsdmf_html_input'] == "date"){
            switch($list_field['xsdmf_date_type']){
                case 1:
                    $output['html'] = $this->_getSelectInputYear();
                    break;
                default:
                    $output['date_type'] = "full_date";
                    $output['html'] = $this->_getSelectInputDate();
            }

        // Other input types
        }else {
            $output['html']= $this->_getSelectInputGeneral($result);
        }


        return $output;
    }


    function najaxGetMeta()
    {
        NAJAX_Client::mapMethods($this, array('getSearchKeyOptions'));
        NAJAX_Client::publicMethods($this, array('getSearchKeyOptions'));
    }


    /**
     * Returns an array of value/text for input type that have matching value on xsd_display_matchfields table
     * @param array $result
     * @return string
     */
    protected function _getSelectInputGeneral($result = array())
    {
        $this->tpl->assign("options", $result);
        $output = $this->tpl->getTemplateContents();

        return $output;
    }


    /**
     * Returns options of year select drop down
     * @return string
     */
    protected function _getSelectInputYear()
    {

        $result = array();
        $start_year = 1900;
        $end_year = strftime("%Y") + 3;
        for ($year = $end_year; $year >= $start_year; $year--){
            $result[$year] = $year;
        }

        $this->tpl->assign("options", $result);
        $output = $this->tpl->getTemplateContents();

        return $output;
    }


    /**
     * Returns select fields date (mm-dd-yyyy)
     * @return string
     */
    protected function _getSelectInputDate()
    {
        $reverse_years = true;
        $start_year = '1900';
        $end_year = strftime("%Y") + 3;
        $display_days = true;
        $display_months =true;
        $display_years = true;
        $full_date = "0000-00-00";

        // Rewrite pre-set value of the expected type of input field
        $this->tpl->assign('field_type', "select_date");

        $this->tpl->assign('rel_url', APP_RELATIVE_URL);
        $this->tpl->assign("reverse_years", $reverse_years);
        $this->tpl->assign("start_year", $start_year);
        $this->tpl->assign("end_year", $end_year);
        $this->tpl->assign("display_days", $display_days);
        $this->tpl->assign("display_months", $display_months);
        $this->tpl->assign("display_years", $display_years);
        $this->tpl->assign("full_date", $full_date);

        $output = $this->tpl->getTemplateContents();

        return $output;
    }

}
