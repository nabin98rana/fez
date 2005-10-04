<?php
include_once("../config.inc.php");

include_once(APP_INC_PATH . "class.template.php");
include_once(APP_INC_PATH . "class.auth.php");
include_once(APP_INC_PATH . "class.user.php");
include_once(APP_INC_PATH . "class.record.php");
include_once(APP_INC_PATH . "class.misc.php");
include_once(APP_INC_PATH . "class.setup.php");
include_once(APP_INC_PATH . "db_access.php");
include_once(APP_INC_PATH . "class.collection.php");
include_once(APP_INC_PATH . "class.community.php");
include_once(APP_INC_PATH . "class.date.php");
include_once(APP_INC_PATH . "class.doc_type_xsd.php");
include_once(APP_INC_PATH . "class.workflow_trigger.php");
include_once(APP_INC_PATH . "class.fedora_api.php");
include_once(APP_INC_PATH . "class.xsd_html_match.php");
include_once(APP_INC_PATH . "najax/najax.php");

/**
 * SelectRecord
 * This class maps into the javascript through NAJAX.  The javascript in the template populates the select boxes 
 * using calls to this class through NAJAX.
 */
class SelectRecord {

    function getCollections($community_pid)
    {
        $collections = Collection::getList($community_pid);
        $list = array();
        foreach($collections['list'] as $item) {
            $pid = $item['pid'];
            $list[] = array('value' => $pid, 'text' => Misc::stripOneElementArrays($item['title']));
        }
        return $list;
    }

    function getRecords($collection_pid)
    {
	$listing = Collection::getListing($collection_pid);
        $list = array();
        foreach ($listing['list'] as $item) {
            $list[] = array('text' => Misc::stripOneElementArrays($item['title']), 'value' => $item['pid']);
        }
        return $list;
    }
    
    function najaxGetMeta()
    {
        NAJAX_Client::mapMethods($this, array('getCollections', 'getRecords'));
        NAJAX_Client::publicMethods($this, array('getCollections', 'getRecords'));
    }
}

NAJAX_Server::allowClasses('SelectRecord');
if (NAJAX_Server::runServer()) {
	exit;
}


$tpl = new Template_API();
$tpl->setTemplate("workflow/index.tpl.html");
$tpl->assign("type", "select_record");
$tpl->assign("type_name", "Select Record");

Auth::checkAuthentication(APP_SESSION);
$isUser = Auth::getUsername();
$tpl->assign("isUser", $isUser);
$isAdministrator = User::isUserAdministrator($isUser);
$tpl->assign("isAdministrator", $isAdministrator);
$id = Misc::GETorPOST('id');
$tpl->assign("id", $id);
$wfs_id = Misc::GETorPOST('wfs_id');
$wfstatus = WorkflowStatusStatic::getSession($id); // restores WorkflowStatus object from the session

$tpl->assign('workflow_buttons', $wfstatus->getButtons());
$cat = Misc::GETorPOST('cat');
if ($cat == 'submit') {
    $wfstatus->pid = Misc::GETorPOST('pid');
    $wfstatus->parent_pid = Misc::GETorPOST('collection_pid');
}
$wfstatus->checkStateChange();


$communities = Community::getList();
$communities_list = Misc::keyPairs($communities['list'], 'pid', 'title');
$communities_list = Misc::stripOneElementArrays($communities_list);
$tpl->assign('communities_list', $communities_list);
$tpl->assign('communities_list_selected', $communities['list'][0]['pid']);
$tpl->assign('najax_header', NAJAX_Utilities::header(APP_RELATIVE_URL.'include/najax'));
$tpl->assign('najax_register', NAJAX_Client::register('SelectRecord', 'select_record.php'));

$tpl->displayTemplate();
?>
