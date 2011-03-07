{literal}

function createRecord(f, xdis_id_sel) {
    f.action = rel_url + 'workflow/new.php';
    xdis_id = getSelectedOption(f, xdis_id_sel.getAttribute('name'));
    f.xdis_id.setAttribute('value', xdis_id);
    f.submit();
}

function assignWorkflow(f) {
    var features = 'width=420,height=200,top=30,left=30,resizable=no,scrollbars=yes,toolbar=no,location=no,menubar=no,status=no';
    var popupWin = window.open('', '_popup', features);
    popupWin.focus();
    f.target = '_popup';
    f.action = rel_url + 'popup.php';
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

function assignItems(f)
{
    if (!hasOneChecked(f, 'item[]')) {
        alert('Please choose which entries to assign.');
        return false;
    }
    if (f.users.options[f.users.selectedIndex].value == '') {
        alert('Please choose the user to assign these entries to.');
        f.users.focus();
        selectField(f, 'users');
        return false;
    }
    var features = 'width=420,height=200,top=30,left=30,resizable=no,scrollbars=yes,toolbar=no,location=no,menubar=no,status=no';
    var popupWin = window.open('', '_popup', features);
    popupWin.focus();
    f.action = rel_url + 'popup.php';
    f.target = '_popup';
    f.submit();
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

{/literal}
