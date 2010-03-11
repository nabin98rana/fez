
function createRecord(f, xdis_id_sel) 
{
    f.action = rel_url + 'workflow/new.php';
    xdis_id = getSelectedOption(f, xdis_id_sel.getAttribute('name'));
    f.xdis_id.setAttribute('value', xdis_id);
    f.submit();
}

function checkDeleteRecords(f)
{
    if (!hasOneChecked(f, 'pids[]')) {
        alert('Please select at least one item.');
        return false;
    }
    
    if (!confirm('The checked items will be deleted, are you sure?')) {
        return false;
    } else {
	
		// ask the user for a history comment
		var historyComment = window.prompt("Please enter a reason for the bulk delete (leave blank for no comment)","");
    
	 	textField = document.createElement('INPUT');
	 	textField.type = 'hidden';
	 	textField.setAttribute('value', historyComment);
	 	textField.setAttribute('Name', 'historyComment');
		textField.id = 'historyComment';
	 	f.appendChild(textField);

        f.cat.value = 'delete_objects';
        f.action = rel_url + 'popup.php';
        f.target = '_popup';
        f.method = 'post';
            
        var features = 'width=420,height=200,top=30,left=30,resizable=no,scrollbars=yes,toolbar=no,location=no,menubar=no,status=no';
        var popupWin = window.open('', '_popup', features);
        popupWin.focus();
    
        f.submit();        
        
    }
}

function orderAndSort(f, field1, field2) 
{
    sort_by = getSelectedOption(f, field1);
    sort_order = getSelectedOption(f, field2);
    sortURL = page_url + '?' + url_wo_sort + 'sort_by=' + sort_by + '&sort_order=' + sort_order;
    window.location.href = sortURL;
}

function resizePagerNew(f)
{
    var pagesize = f.rows.options[f.rows.selectedIndex].value;
    var pagingURL = page_url + '?' + url_wo_rows + 'rows=' + pagesize + '&pager_row=0';
    window.location.href = pagingURL;
}

function setTemplateNew(f, field) {
    var tpl = getSelectedOption(f, field);
    var templateURL = page_url + '?' + url_wo_tpl + 'tpl=' + tpl;
    window.location.href = templateURL;
}