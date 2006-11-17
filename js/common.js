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