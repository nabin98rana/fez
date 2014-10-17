<!--
var today = new Date();
var expires = new Date(today.getTime() + (56 * 86400000));

function closeAndRefresh()
{
    opener.location.href = opener.location;
    window.close();
}

function closeAndGotoList()
{
    opener.location.href = 'list.php';
    window.close();
}

function hideClosed(f)
{
    if (f.hide_closed.checked) {
        window.location.href = page_url + "?" + replaceParam(window.location.href, 'hide_closed', '1');
    } else {
        window.location.href = page_url + "?" + replaceParam(window.location.href, 'hide_closed', '0');
    }
}

function filePermissionsChange(descriptionNum) {
    descriptionValue = document.getElementsByName('description[' + descriptionNum + ']')[0].value;
    if (descriptionValue == '' || descriptionValue == 'Full text (open access)' || descriptionValue == 'HERDC evidence - not publicly available') {
        if (document.getElementsByName('filePermissions[]')[descriptionNum].selectedIndex != 0) {
            if (document.getElementsByName('filePermissions[]')[descriptionNum].selectedIndex == 5) {
                document.getElementsByName('description[' + descriptionNum + ']')[0].value = 'HERDC evidence - not publicly available';
            } else if (document.getElementsByName('filePermissions[]')[descriptionNum].selectedIndex == 7) {
                document.getElementsByName('description[' + descriptionNum + ']')[0].value = 'Non-traditional research output Research Statement - not publicly available';
            } else {
                document.getElementsByName('description[' + descriptionNum + ']')[0].value = 'Full text (open access)';
            }
        }
    }

    if (document.getElementsByName('filePermissions[]')[descriptionNum].selectedIndex == 5 || document.getElementsByName('filePermissions[]')[descriptionNum].selectedIndex == 8) {
        document.getElementById('open_access_release_date[' + descriptionNum + ']').style.display = "none";
        document.getElementById('datepicker' + descriptionNum).value = '';
    } else {
        document.getElementById('open_access_release_date[' + descriptionNum + ']').style.display = "block";
    }

}

function filePermissionsChangeNew(descriptionNum) {
    descriptionValue = document.getElementsByName('description[' + descriptionNum + ']')[0].value;

    if (descriptionValue == '' || descriptionValue == 'Full text (open access)' || descriptionValue == 'HERDC evidence - not publicly available') {
        if (document.getElementById('filePermissionsNew['+ descriptionNum + ']').selectedIndex != 0) {
            if (document.getElementById('filePermissionsNew['+ descriptionNum + ']').selectedIndex == 5) {
                document.getElementsByName('description[' + descriptionNum + ']')[0].value = 'HERDC evidence - not publicly available';
            } else if (document.getElementById('filePermissionsNew['+ descriptionNum + ']').selectedIndex == 7) {
                document.getElementsByName('description[' + descriptionNum + ']')[0].value = 'Non-traditional research output Research Statement - not publicly available';
            } else {
                document.getElementsByName('description[' + descriptionNum + ']')[0].value = 'Full text (open access)';
            }
        }
    }
    if (document.getElementById('filePermissionsNew['+ descriptionNum + ']').selectedIndex == 5 || document.getElementById('filePermissionsNew['+ descriptionNum + ']').selectedIndex == 8) {
        document.getElementById('open_access_release_date[' + descriptionNum + ']').style.display = "none";
        document.getElementById('datepickerSWFUpload_0_' + descriptionNum).value = '';
    } else {
        document.getElementById('open_access_release_date[' + descriptionNum + ']').style.display = "block";
    }
}


function unhideRow(element_name, table_name)
{
	var firstHiddenRow = 0;
	var emptyRowExists = 0;
	var x = 0;
	// for each row in the set of elements (with _x suffix)
	for(row = document.getElementById('tr_' + element_name + '_' + x);
		row != null && row != false;
		row = document.getElementById('tr_' + element_name + '_' +x ))
	{
		var rowInput = document.getElementById(element_name + '_' + x);

		// are there any visible rows that have no values?
		// if there are already visible blank rows, then we don't need to do anything
		if (row.style.display == '' && rowInput.value == '') {
			emptyRowExists = 1;
		}
		// look for the first hidden row
		if (row.style.display != '') {
			firstHiddenRow = x;
			break;
		}
		x++;
	}

	if (firstHiddenRow == 0 && emptyRowExists == 0) {
		var show_tr = document.getElementById('tr_' + element_name + '_' + firstHiddenRow);
		if (show_tr != null) {
			show_tr.style.display = '';
			var show_arrow = document.getElementById(element_name + '_' + (firstHiddenRow-1)+'_arrow');
			if (show_arrow != null) {
				show_arrow.style.display = 'inline';
			}
		}
	}

	// did we find a hidden one?
	if (firstHiddenRow > 0 && emptyRowExists == 0) {
		var show_tr = document.getElementById('tr_' + element_name + '_' + firstHiddenRow);
		show_tr.style.display = '';

		var arrow = document.getElementById(element_name + '_' + (firstHiddenRow-1)+'_arrow');
		if( arrow ) arrow.style.display = 'inline';
	}
}

function str_replace(s, srch, rplc) {
  var tmp = s;
  var tmp_before = new String();
  var tmp_after = new String();
  var tmp_output = new String();
  var int_before = 0;
  var int_after = 0;

  while (tmp.toUpperCase().indexOf(srch.toUpperCase()) > -1) {
    int_before = tmp.toUpperCase().indexOf(srch.toUpperCase());
    tmp_before = tmp.substring(0, int_before);
    tmp_output = tmp_output + tmp_before;
    tmp_output = tmp_output + rplc;
    int_after = tmp.length - srch.length + 1;
    tmp = tmp.substring(int_before + srch.length);
  }

  return tmp_output + tmp;
}

function removeOptionByValue(f, field_name, value)
{
    var field = getFormElement(f, field_name);
    for (var i = 0; i < field.options.length; i++) {
        if (field.options[i].value == value) {
            field.options[i] = null;
        }
    }
}

function getTotalCheckboxes(f, field_name)
{
    var total = 0;
    for (var i = 0; i < f.elements.length; i++) {
        if (f.elements[i].name == field_name) {
            total++;
        }
    }
    return total;
}

function getTotalCheckboxesChecked(f, field_name)
{
    var total = 0;
    for (var i = 0; i < f.elements.length; i++) {
        if ((f.elements[i].name == field_name) && (f.elements[i].checked)) {
            total++;
        }
    }
    return total;
}

function showComboBoxes()
{
    for (var i = 0; i < document.forms.length; i++) {
        for (var y = 0; y < document.forms[i].elements.length; y++) {
            if (((document.forms[i].elements[y].type == 'select-one') ||
                 (document.forms[i].elements[y].type == 'select-multiple')) &&
                    (document.forms[i].elements[y].name != 'lookup') &&
                    (document.forms[i].elements[y].name != 'lookup[]')) {
                document.forms[i].elements[y].style.visibility = 'visible';
            }
        }
    }
}

function lookupField(f, search_field, field_name, callbacks)
{
    var search = search_field.value;
    if (isWhitespace(search)) {
        return false;
    }
    var target_field = getFormElement(f, field_name);
    for (var i = 0; i < target_field.options.length; i++) {
        var value = target_field.options[i].text.toUpperCase();
        if (startsWith(value, search.toUpperCase())) {
            // if we are targetting a multiple select box, then unselect everything
            // before selecting the matched option
            if (target_field.type == 'select-multiple') {
                clearSelectedOptions(target_field);
            }
            target_field.options[i].selected = true;
            // handle calling any callbacks
            if (callbacks != null) {
                for (var y = 0; y < callbacks.length; y++) {
                    eval(callbacks[y] + ';');
                }
            }
            return true;
        }
    }
    target_field.selectedIndex = 0;
}

function clearSelectedOptions(field)
{
    field.options[0].selected = true;
    for (var i = 0; i < field.options.length; i++) {
        field.options[i].selected = false;
    }
}

function selectAllOptions(f, field_name)
{
	var field = getFormElement(f, field_name);
    for (var y = 0; y < field.options.length; y++) {
        field.options[y].selected = true;
    }
}

function selectOptions(f, field_name, values)
{
    var field = getFormElement(f, field_name);
    for (var i = 0; i < values.length; i++) {
        for (var y = 0; y < field.options.length; y++) {
            if (field.options[y].value == values[i].value) {
                field.options[y].selected = true;
            }
        }
    }
}

function selectCustomOptions(f, field_name, values)
{
    var field = getFormElement(f, field_name);
    for (var i = 0; i < values.length; i++) {
        for (var y = 0; y < field.options.length; y++) {
            if (field.options[y].value == values[i]) {
                field.options[y].selected = true;
            }
        }
    }
}

function small_window(myurl) {
	var newWindow;
	var props = 'scrollBars=yes,resizable=yes,toolbar=no,menubar=no,location=no,directories=no,top=50,left=100,width=800,height=550';
	newWindow = window.open(myurl, "Add_from_Src_to_Dest", props);
	newWindow.focus();
}

// Adds the list of selected items selected in the child
// window to its list. It is called by child window to do so.
function addToParentList(sourceList, destinationList) {
//	destinationList = window.document.report_form.parentList;
//	destinationList = parentList;

	for(var count = destinationList.options.length - 1; count >= 0; count--) {
		for(var i = 0; i < sourceList.options.length; i++) {
			if (destinationList.options[count]) {
				if ((sourceList.options[i] != null) && (sourceList.options[i].selected) && (destinationList.options[count].value == sourceList.options[i].value)) {
					destinationList.options[count] = null;
				}
			}
		}
	}
	var len = destinationList.length;
	for(var i = 0; i < sourceList.options.length; i++) {
		if ((sourceList.options[i] != null) && (sourceList.options[i].selected)) {
		   destinationList.options[len] = new Option(sourceList.options[i].text, sourceList.options[i].value );
		   destinationList.options[len].selected = true;
		   len++;
		}
   	}
}
// Deletes the selected items of supplied list.
function deleteSelectedItemsFromList(sourceList) {
	var maxCnt = sourceList.options.length;
	for(var i = maxCnt - 1; i >= 0; i--) {
		if ((sourceList.options[i] != null) && (sourceList.options[i].selected == true)) {
			sourceList.options[i] = null;
      		}
   	}
}

function selectOption(f, field_name, value)
{
    for (var i = 0; i < f.elements.length; i++) {
        if (f.elements[i].name == field_name) {
			field = f.elements[i];
            for (var i = 0; i < field.options.length; i++) {
                if (field.options[i].value == value) {
                    field.options[i].selected = true;
                    return true;
                }
            }
			return false;
        }
    }
	return false;
}

function setHiddenFieldValue(f, field_name, value)
{
    var field = getFormElement(f, field_name);
    field.value = value;
}

function getForm(form_name)
{
    for (var i = 0; i < document.forms.length; i++) {
        if (document.forms[i].name == form_name) {
            return document.forms[i];
        }
    }
}

function getPageElement(name)
{
    if (document.getElementById) {
        return document.getElementById(name);
    } else if (document.all) {
        return document.all[name];
    }
}

function getOpenerPageElement(name)
{
    if (window.opener.document.getElementById) {
        return window.opener.document.getElementById(name);
    } else if (window.opener.document.all) {
        return window.opener.document.all[name];
    }
}

function getFormElement(f, field_name, num)
{
    var y = 0;
    for (var i = 0; i < f.elements.length; i++) {
        if (num != null) {
            if (f.elements[i].name == field_name) {
                if (y == num) {
                    return f.elements[i];
                }
                y++;
            }
        } else {

			if (f.elements[i].name == field_name) {
				return f.elements[i];
            }
        }
    }
    return false;
}

function getFormElementByID(f, field_id, num)
{
	return document.getElementById(field_id);
}


function getSelectedItems(field)
{
    var selected = new Array();
    for (var i = 0; i < field.options.length; i++) {
        if (field.options[i].selected) {
            selected[selected.length] = field.options[i];
        }
    }
    return selected;
}

function removeAllOptions(f, field_name)
{
    var field = getFormElement(f, field_name);
	if (field.options == null) { return false; }
	if (field.options.length > 0) {
        field.options[0] = null;
        removeAllOptions(f, field_name);
    }
}

function getValues(list)
{
    var values = new Array();
    for (var i = 0; i < list.length; i++) {
        values[values.length] = list[i].value;
    }
    return values;
}

function optionExists(field, option)
{
	if (field.options == null) { return false; }
	for (var i = 0; i < field.options.length; i++) {
        if (field.options[i].text == option.text) {
            return true;
        }
    }
    return false;
}

function optionValueExists(field, value)
{
	if (field.options == null) { return false; }
	for (var i = 0; i < field.options.length; i++) {
        if (field.options[i].value == value) {
            return true;
        }
    }
    return false;
}

function addOptions(f, field_name, options)
{
    var field = getFormElement(f, field_name);
	if (field.options == null) { return false; }
	for (var i = 0; i < options.length; i++) {
        if (!optionExists(field, options[i])) {
            field.options.length = field.options.length + 1;
            field.options[field.options.length-1].text = options[i].text;
            field.options[field.options.length-1].value = options[i].value;
            field.options[field.options.length-1].selected = options[i].selected;
		}
    }
}

function replaceParam(str, param, new_value)
{
    if (str.indexOf("?") == -1) {
        return param + "=" + new_value;
    } else {
        var pieces = str.split("?");
        var params = pieces[1].split("&");
        var new_params = new Array();
        for (var i = 0; i < params.length; i++) {
            if (params[i].indexOf(param + "=") == 0) {
                params[i] = param + "=" + new_value;
            }
            new_params[i] = params[i];
        }
        // check if the parameter doesn't exist on the URL
        if ((str.indexOf("?" + param + "=") == -1) && (str.indexOf("&" + param + "=") == -1)) {
            new_params[new_params.length] = param + "=" + new_value;
        }
        return new_params.join("&");
    }
}

function checkRadio(form_name, field_name, num)
{
    var f = getForm(form_name);
    var field = getFormElement(f, field_name, num);
    field.checked = true;
}

function toggleCheckbox(form_name, field_name, num)
{
    var f = getForm(form_name);
    var checkbox = getFormElement(f, field_name, num);
    if (checkbox.disabled) {
        return false;
    }
    if (checkbox.checked) {
        checkbox.checked = false;
    } else {
        checkbox.checked = true;
    }
}

var toggle = 'off';
function toggleSelectAll(f, field_name)
{
    for (var i = 0; i < f.elements.length; i++) {
        if (f.elements[i].name == field_name) {
            if (toggle == 'off') {
                f.elements[i].checked = true;
            } else {
                f.elements[i].checked = false;
            }
        }
    }
    if (toggle == 'off') {
        toggle = 'on';
    } else {
        toggle = 'off';
    }
}

function isElementVisible(element)
{
    if (element != null && ((!element.style.display) || (element.style.display == getDisplayStyle()))) {
        return true;
    } else {
        return false;
    }
}

function toggleVisibility(title, keep_basic_filter_form, create_cookie)
{
    var element = getPageElement(title + '1');
	if (element == null) {
    	var element = getPageElement('tr_' + title);
		if (element == null) {
	    	var element = getPageElement(title);
			if (element != null) {
			    if (isElementVisible(element)) {
			        var new_style = 'none';
			    } else {
			        var new_style = getDisplayStyle();
			    }
				element.style.display = new_style;
				return true;
			} else {
				return false;
			}
		} else {
		    if (isElementVisible(element)) {
		        var new_style = 'none';
		    } else {
		        var new_style = getDisplayStyle();
		    }
			element.style.display = new_style;
			return true;
		}
    }

	if (element != null) {
	    if (isElementVisible(element)) {
	        var new_style = 'none';
	        if (title != 'basic_filter_form' && keep_basic_filter_form != 1) {
	            var basic_element = getPageElement('basic_filter_form' + '1');
				if (isElementVisible(basic_element)) {
					toggleVisibility('basic_filter_form');
				}
			}

	    } else {
	        var new_style = getDisplayStyle();
	        if (title != 'basic_filter_form' && keep_basic_filter_form != 1) {
	            var basic_element = getPageElement('basic_filter_form' + '1');
				if (!isElementVisible(basic_element)) {
					toggleVisibility('basic_filter_form');
				}
			}
	    } //
	    var i = 1;
	    while (1) {
	        element = getPageElement(title + i);
	        if (!element) {
	            break;
	        }
	        element.style.display = new_style;
	        i++;
	    }
	}
    // if any elements were found, then...
    if (i > 1) {
        var link_element = getPageElement(title + '_link');
        if (link_element) {
            if (new_style == 'none') {
                link_element.innerHTML = 'show';
                link_element.title = 'show details about this section';
            } else {
                link_element.innerHTML = 'hide';
                link_element.title = 'hide details about this section';
            }
        }
    }
    if (((create_cookie == null) || (create_cookie == false)) && (create_cookie != undefined)) {
        return false;
    } else {
        setCookie('visibility_' + title, new_style, expires);
    }
}

function getDisplayStyle()
{
        return 'block';
}

function getCookie(name)
{
    var start = document.cookie.indexOf(name+"=");
    var len = start+name.length+1;
    if ((!start) && (name != document.cookie.substring(0,name.length))) return null;
    if (start == -1) return null;
    var end = document.cookie.indexOf(";",len);
    if (end == -1) end = document.cookie.length;
    return unescape(document.cookie.substring(len,end));
}

function setCookie(name, value, expires, path, domain, secure)
{
    document.cookie = name + "=" +escape(value) +
        ( (expires) ? ";expires=" + expires.toGMTString() : "") +
        ( (path) ? ";path=" + path : "") +
        ( (domain) ? ";domain=" + domain : "") +
        ( (secure) ? ";secure" : "");
}

function openHelp(rel_url, topic)
{
    var width = 550;
    var height = 500;
    var w_offset = 30;
    var h_offset = 30;
    var location = 'top=' + h_offset + ',left=' + w_offset + ',';
    if (screen.width) {
        location = 'top=' + h_offset + ',left=' + (screen.width - (width + w_offset)) + ',';
    }
    var features = 'width=' + width + ',height=' + height + ',' + location + 'resizable=no,scrollbars=yes,toolbar=no,location=no,menubar=no,status=no';
    var helpWin = window.open(rel_url + 'help.php?topic=' + topic, '_help', features);
    helpWin.focus();
}

function getFlashMessage(id)
{
    wfs = new Session();
    wfs.getMessage(function(s) {
        if (s != null && s.length > 0) {
            var x = noty({
                text: s,
                type: 'warning',
                dismissQueue: true,
                modal: false,
                layout: 'topCenter',
                timeout: 5000,
                theme: 'defaultTheme'
            });
        }
    });
    wfs.clearMessage();
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


function setAction(f,action)
{
	fe = getFormElement(f,'action');
	if (fe == null || fe == false) {
		fe = document.createElement('input');
		fe.setAttribute('name','action');
		fe.setAttribute('type','hidden');
		f.appendChild(fe);
	}
	fe.setAttribute('value', action);
	return true;
}

function workflowItems(f, rel_url)
{
    if (!hasOneChecked(f, 'pids[]')) {
        alert('Please choose which entries to run this workflow against.');
        return false;
    }
    if (f.wft_id.options[f.wft_id.selectedIndex].value == '') {
        alert('Please choose the bulk workflow to use these objects.');
        f.users.focus();
        selectField(f, 'wft_id');
        return false;
    }

    f.cat.value = 'select_workflow';
    f.action = rel_url + 'workflow/bulk_change.php';
    f.target='';
    f.method='post';
    f.submit();
}

function workflowBulkChangeSearch(f, rel_url)
{
    if (f.wft_id.options[f.wft_id.selectedIndex].value == '') {
        alert('Please choose the bulk workflow to use these objects.');
        f.users.focus();
        selectField(f, 'wft_id');
        return false;
    }
    if (!confirm('This will run ' + f.wft_id.options[f.wft_id.selectedIndex].text + ' on all objects in the search result.  Are you sure?')) {
    	return false;
	}

    f.cat.value = 'select_workflow';
    f.action = rel_url + 'workflow/bulk_change_from_search.php';
    f.target='';
    f.method='post';
    f.submit();
}

function toggleDateFields(f, field_name)
{
    var checkbox = getFormElement(f, 'filter[' + field_name + ']');
    var filter_enabled = getFormElement(f, field_name + '[filter_enabled]');
    var filter_type = getFormElement(f, field_name + '[filter_type]');
    var month_field = getFormElement(f, field_name + '[start][Month]');
    var day_field = getFormElement(f, field_name + '[start][Day]');
    var year_field = getFormElement(f, field_name + '[start][Year]');

    var month_end_field = getFormElement(f, field_name + '[end][Month]');
        if (month_end_field == false) {
            var pre_element_name = field_name.substring(0, field_name.indexOf('['));
            var month_end_field = getFormElement(f, pre_element_name + '[end][Month]');
        }
    var day_end_field = getFormElement(f, field_name + '[end][Day]');
        if (day_end_field == false) {
            var pre_element_name = field_name.substring(0, field_name.indexOf('['));
            var day_end_field = getFormElement(f, pre_element_name+'[end][Day]');
        }
    var year_end_field = getFormElement(f, field_name + '[end][Year]');
        if (year_end_field == false) {
            var pre_element_name = field_name.substring(0, field_name.indexOf('['));
            var year_end_field = getFormElement(f, pre_element_name+'[end][Year]');
        }
    if (checkbox.checked) {
        var disable = false;
    } else {
        var disable = true;
    }
    filter_enabled.value = disable?'0':'1';
    filter_type.disabled = disable;
    month_field.disabled = disable;
    day_field.disabled = disable;
    year_field.disabled = disable;
    month_end_field.disabled = disable;
    day_end_field.disabled = disable;
    year_end_field.disabled = disable;
}

function checkDateFilterType(f, type_field)
{
    var option = getSelectedOption(f, type_field.name);
    var element_name = type_field.name.substring(0, type_field.name.indexOf('[filter'));
    var element = getPageElement('tr_'+element_name);
    if ((option == 'between') && (!isElementVisible(element))) {
        toggleVisibility(element_name, false);
    } else if ((option != 'between') && (isElementVisible(element))) {
        toggleVisibility(element_name, false);
    }
}
function selectDateOptions(field_prefix, date_str)
{
    if (date_str.length != 10) {
        return false;
    } else {
        var year = date_str.substring(0, date_str.indexOf('-'));
        var month = date_str.substring(date_str.indexOf('-')+1, date_str.lastIndexOf('-'));
        var day = date_str.substring(date_str.lastIndexOf('-')+1);
        selectDateField(field_prefix, day, month, year);
    }
}
function selectDateField(field_name, day, month, year)
{
    selectOption(this.document.custom_filter_form, field_name + '[Day]', day);
    selectOption(this.document.custom_filter_form, field_name + '[Month]', month);
    selectOption(this.document.custom_filter_form, field_name + '[Year]', year);
}

function confirmDelete() {
    if (confirm("This action will delete this object. Are you certain you wish to do this?")) {
        return true;
    } else {
        return false;
	}
}

function swapTextBox(textbox, xsdmf_id, loopnum, direction)
{
    var swapLoopNumId = (parseInt(loopnum) + parseInt(direction));

    var curTextbox = document.getElementById(textbox + '_' + loopnum);
    var swapTextbox = document.getElementById(textbox + '_' + swapLoopNumId);

    if(curTextbox == null || swapTextbox == null)
        return;

    var tmpValue = curTextbox.value;

    curTextbox.value = swapTextbox.value;
    swapTextbox.value = tmpValue;

    var curDropDown = document.getElementById('xsd_display_fields_'  + xsdmf_id + '_' + loopnum);
    var prevDropDown = document.getElementById('xsd_display_fields_' + xsdmf_id + '_' + swapLoopNumId);

    if(curDropDown && prevDropDown)
    {
        swapDropDownValues(curDropDown, prevDropDown);
    }
}

function swapDropDowns(dropDownOne, DropDownTwo) {
    var dd1 = document.getElementById(dropDownOne);
	var dd2 = document.getElementById(DropDownTwo);
    if (dd1 && dd2) {
        swapDropDownValues(dd1, dd2);
    }
}

function swapDropDownValues(dropDownOne, DropDownTwo)
{
    var tmpDropDownText = dropDownOne.options[dropDownOne.selectedIndex].text;
    var tmpDropDownVal = dropDownOne.options[dropDownOne.selectedIndex].value;

    dropDownOne.options[dropDownOne.selectedIndex].text = DropDownTwo.options[DropDownTwo.selectedIndex].text;
    dropDownOne.options[dropDownOne.selectedIndex].value = DropDownTwo.options[DropDownTwo.selectedIndex].value;

    DropDownTwo.options[DropDownTwo.selectedIndex].text = tmpDropDownText;
    DropDownTwo.options[DropDownTwo.selectedIndex].value = tmpDropDownVal;
}

/**
 * Dynamically load a javascript file
 * @param filename
 */
function loadJsFile(filename)
{
    var file_include = document.createElement('script');
    file_include.setAttribute("type","text/javascript");
    file_include.setAttribute("src", filename);

    document.getElementsByTagName("head")[0].appendChild(file_include);
}


//-->