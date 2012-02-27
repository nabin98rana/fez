<script type="text/javascript">
<!--
var page_url = '{$page_url}';
var last_page = {if $items_info.last_page != ""}{$items_info.last_page}{else}0{/if};
{literal}
function checkDeleteRecords(f)
{
    if (!hasOneChecked(f, 'pids[]')) {
        alert('Please select at least one item.');
        return false;
    }
    if (!confirm('The checked items will be deleted, are you sure?')) {
        return false;
    } else {
        var features = 'width=420,height=200,top=30,left=30,resizable=no,scrollbars=yes,toolbar=no,location=no,menubar=no,status=no';
        var popupWin = window.open('', '_popup', features);
        popupWin.focus();
        return true;
    }
}
function checkPublishRecords(f)
{
    if (!hasOneChecked(f, 'pids[]')) {
        alert('Please select at least one item.');
        return false;
    }
    if (!confirm('The checked items will be published, are you sure?')) {
        return false;
    } else {
	  	
        cat = getFormElement(f, 'cat');
        cat.value = 'publish_objects';  
        var features = 'width=420,height=200,top=30,left=30,resizable=no,scrollbars=yes,toolbar=no,location=no,menubar=no,status=no';
        var popupWin = window.open('', '_popup', features);
        popupWin.focus();
        return true;
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

function disableFields(f)
{
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

function sortList(f, field) {
    sort_by = getSelectedOption(f, field);
    sort_by_dir = getSelectedOption(f, 'sort_by_dir');    
    temp = page_url + "?" + 'sort_by=' + sort_by;
    window.location.href = temp + "&" + 'sort_by_dir=' + sort_by_dir;
}

function resizePagerMyFez(f, page_url)
{
    var pagesize = f.page_size.options[f.page_size.selectedIndex].value;   
    window.location.href = page_url + "&rows=" + pagesize + "&pager_row=0";
}

{/literal}
// -->
</script>