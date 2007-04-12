function handleXSDMF_Editor(pid, xsdmf_id, vidx)
{
	if (vidx.length < 1) {
		vidx = '0';
	}
	safe_pid = pid.replace(/:/,"_");
	mess = document.getElementById('xsdmf_editor_mess_'+xsdmf_id+'_'+safe_pid+'_'+vidx);
	sUpdating = 'Updating...';
    if (mess.innerHTML == sUpdating) {
        return;
    } 
    mess.innerHTML = sUpdating;
    mess.style.backgroundColor = 'red';
    mess.style.color = 'white';
    mess.style.display = '';
	newValue = document.getElementById('xsdmf_editor_input_'+xsdmf_id+'_'+safe_pid+'_'+vidx).value;
    ajax_obj = new NajaxRecord();
    ajax_obj.onSetValueError = function() {
        mess = document.getElementById('xsdmf_editor_mess_'+xsdmf_id+'_'+safe_pid+'_'+vidx);
        mess.innerHTML = 'Timeout or Ajax Error';
        mess.style.backgroundColor = 'red';
        setTimeout("document.getElementById('xsdmf_editor_mess_"+xsdmf_id+'_'+safe_pid+'_'+vidx+"').style.display='none'", 5000);
    };
    ajax_obj.setPid(pid);
    ajax_obj.setValue(xsdmf_id, newValue, vidx, function(isOk) {
        mess = document.getElementById('xsdmf_editor_mess_'+xsdmf_id+'_'+safe_pid+'_'+vidx);
        if (isOk) {
            mess.innerHTML = 'Done';
            mess.style.backgroundColor = 'green';
        } else {
            mess.innerHTML = 'Failed';
            mess.style.backgroundColor = 'red';
        }
        setTimeout("document.getElementById('xsdmf_editor_mess_"+xsdmf_id+'_'+safe_pid+'_'+vidx+"').style.display='none'", 5000);
    });
}

function unhideXSDMF_Editor(pid, xsdmf_id, vidx) {
	safe_pid = pid.replace(/:/,"_");
	div = document.getElementById('xsdmf_editor_div_'+xsdmf_id+'_'+safe_pid+'_'+vidx);
	if (div != null) {
		div.style.display='';
	}
}

function showFlashMessage()
{
	document.getElementById('flash_message_div').style.display = '';
	setTimeout('clearFlashMessage()', 30000);
}

function getFlashMessage(id)
{
	wfs = new Session();
	wfs.getMessage( function(s) {
		if (s != null && s.length > 0) {
			e = document.getElementById('flash_message_div');
			e.innerHTML = s;
			showFlashMessage()
		}
	});
}

function clearFlashMessage()
{
	wfs = new Session();
	wfs.clearMessage();
	document.getElementById('flash_message_div').style.display = 'none';
}

/**
 * Callback for author suggestor to handle the setting of the extra form elements.  This is 
 * called from autosuggest.js in the hideSuggestions method
 */
function authorSuggestorCallback(oThis, oTarget) {
			var dtList = new Array();
			if (isWhitespace(oThis.textboxcopy.value)) {
				oThis.textboxcopy.value = oTarget.firstChild.nodeValue;
			}
			dtList[0] = new Option;
			dtList[0].text = "(none)";
			dtList[0].value = "0";
			dtList[1] = new Option;			
			dtList[1].value = oTarget.getAttribute('id');
			dtList[1].text = oTarget.firstChild.nodeValue+" ("+oTarget.getAttribute('id')+")";
			dtList[1].selected = true;


			if (oThis.textboxcopy == null) {
				oThis.textbox.focus();
			} else {				
				oThis.textboxcopy.focus();
				removeAllOptions(oThis.form, oThis.selectbox);
				addOptions(oThis.form, oThis.selectbox, dtList);
			}		
}

function cloneSuggestorCallback(oThis, oTarget) {
	c = document.getElementById('collection_pid');
	c.value = oTarget.getAttribute('id');
}
