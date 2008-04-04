<script language="JavaScript" type="text/javascript">
<!--

{if $list_type == 'collection_records_list'}
var page_url = '{$rel_url}collection/{$collection_pid}';
{elseif $list_type == 'community_list'}
var page_url = '{$rel_url}list';
{elseif $list_type == 'collection_list'}
var page_url = '{$rel_url}community/{$community_pid}';
{else}
var page_url = '{$rel_url}list';
{/if}
{literal}
var current_page = {/literal}{if $list_info.current_page != ""}{$list_info.current_page}{else}0{/if}{literal};
var last_page = {/literal}{if $list_info.last_page != ""}{$list_info.last_page}{else}0{/if}{literal};
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
    f.action = '{/literal}{$rel_url}{literal}popup.php';
    f.target = '_popup';
    f.submit();
}


function createRecord(f, xdis_id_sel) {
    f.action = '{/literal}{$rel_url}{literal}workflow/new.php';
    xdis_id = getSelectedOption(f, xdis_id_sel.getAttribute('name'));
    f.xdis_id.setAttribute('value', xdis_id);
	f.submit();
}

function assignWorkflow(f) {
    var features = 'width=420,height=200,top=30,left=30,resizable=no,scrollbars=yes,toolbar=no,location=no,menubar=no,status=no';
    var popupWin = window.open('', '_popup', features);
    popupWin.focus();
    f.target = '_popup';
    f.action = '{/literal}{$rel_url}{literal}popup.php';
    f.submit();
}
 
function hideClosed(f)
{
    if (f.hide_closed.checked) {
        window.location.href = page_url + "/" + replaceParam(window.location.href, 'hide_closed', '1');
    } else {
        window.location.href = c + "/" + replaceParam(window.location.href, 'hide_closed', '0');
    }
}

function checkPageField(ev)
{
    // check if the user is trying to submit the form by hitting <enter>
    if (((window.event) && (window.event.keyCode == 13)) ||
            ((ev) && (ev.which == 13))) {
        return false;
    }
}
function goPage(f, new_page)
{
    if ((new_page > last_page+1) || (new_page <= 0) ||
            (new_page == current_page+1) || (!isNumberOnly(new_page))) {
        f.page.value = current_page+1;
        return false;
    }
    setPage(new_page-1);
}
function setPage(new_page)
{
    if ((new_page > last_page) || (new_page < 0) ||
            (new_page == current_page)) {
        return false;
    }
    window.location.href = page_url + "/" + replaceParam(window.location.href, 'pager_row', new_page);
}
function downloadCSV()
{
    var f = this.document.csv_form;
    f.submit();
    return false;
}

function disableFields()
{
    var f = document.list_form;
    if (current_page == 0) {
        f.first.disabled = true;
        f.previous.disabled = true;
    }
    if ((current_page == last_page) || (last_page <= 0)) {
        f.next.disabled = true;
        f.last.disabled = true;
    }
    if ((current_page == 0) && (last_page <= 0)) {
        f.page.disabled = true;
        f.go.disabled = true;
    }
}

function orderAndSort(f, field1, field2) {
	sort_by = getSelectedOption(f, field1);
	sort_order = getSelectedOption(f, field2);
	{/literal}
	sortURL = '{$PAGE_URL}?{if $url_wo_sort != ""}{$url_wo_sort}&{/if}sort_by=' + sort_by + '&sort_order=' + sort_order;
	{literal}
	window.location.href = sortURL;
}

function resizePagerNew(f)
{
	{/literal}
    var pagesize = f.rows.options[f.rows.selectedIndex].value;
	var pagingURL = "{$PAGE_URL}?{if $url_wo_rows != ""}{$url_wo_rows}&{/if}rows=" + pagesize + "&pager_row=0";
    window.location.href = pagingURL;
	{literal}
}

function setTemplateNew(f, field) {
	{/literal}
    var tpl = getSelectedOption(f, field);
	var templateURL = "{$PAGE_URL}?{if $url_wo_tpl != ""}{$url_wo_tpl}&{/if}tpl=" + tpl;
    window.location.href = templateURL;
	{literal}
}

function purgeObject(pid)
{
    if (!confirm('This action will permanently delete the selected object.')) {
        return false;
    } else {
        window.location.replace('{/literal}{$rel_url}workflow/delete.php?pid=' + pid + '&href={$smarty.server.REQUEST_URI|escape:'url'}{literal}');
        return true;
    }
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
    f.action = '{/literal}{$rel_url}{literal}popup.php';
    f.target = '_popup';
	f.method = 'post';
        var features = 'width=420,height=200,top=30,left=30,resizable=no,scrollbars=yes,toolbar=no,location=no,menubar=no,status=no';
        var popupWin = window.open('', '_popup', features);
        popupWin.focus();

    f.submit();        
        
    }
}
var custom_filter_element = getPageElement('custom_filter_form' + '1');
{/literal}
/* {$browse_type} ---- {$list_type} */
{if (($browse_type == "" && $list_type <> "all_records_list") || $browse_type != "")}
{literal}
if (isElementVisible(custom_filter_element)) {
	toggleVisibility('custom_filter_form');
}
{/literal}
{/if}
{literal}
var basic_element = getPageElement('basic_filter_form' + '1');
if ((isElementVisible(basic_element) && isElementVisible(custom_filter_element)) || (!isElementVisible(basic_element) && !isElementVisible(custom_filter_element))) {
	toggleVisibility('basic_filter_form');
} 
{/literal}
-->
</script>