var required_xsd_display_fields = new Array();
var xsd_display_fields = new Array();
var myDataSourceAuthor = new YAHOO.widget.DS_XHR(rel_url+"author_suggest_proxy.php", ["Result","name"]);
var myDataSourcePID = new YAHOO.widget.DS_XHR(rel_url+"pid_suggest_proxy.php", ["Result","name"]);
var myDataSourceConference = new YAHOO.widget.DS_XHR(rel_url+"conference_suggest_proxy.php", ["Result","name"]);
var myDataSourcePublisher = new YAHOO.widget.DS_XHR(rel_url+"publisher_suggest_proxy.php", ["Result","name"]);

// Instantiate first JS Array DataSource
var myServer = rel_url+"suggest_proxy.php";
var mySchema = ["Result","name"];

function deleteDatastream(pid, ds_id)
{
    if (!confirm('This action will delete the selected datastream.')) {
        return false;
    } else {
        var features = 'width=420,height=200,top=30,left=30,resizable=yes,scrollbars=yes,toolbar=no,location=no,menubar=no,status=no';
        var popupWin = window.open(rel_url+'popup.php?cat=delete_datastream&pid=' +pid+ '&dsID=' +ds_id, '_popup', features);
        popupWin.focus();
    }
}

function purgeDatastream(pid, ds_id)
{
    if (!confirm('This action will permanently delete the selected datastream.')) {
        return false;
    } else {
        var features = 'width=420,height=200,top=30,left=30,resizable=yes,scrollbars=yes,toolbar=no,location=no,menubar=no,status=no';
        var popupWin = window.open(rel_url+'popup.php?cat=purge_datastream&pid=' +pid+ '&dsID=' +ds_id, '_popup', features);
        popupWin.focus();
    }
}

function toggleDateFields(f, field_name)
{
    var checkbox = getFormElement(f, 'filter[' + field_name + ']');
    var filter_type = getFormElement(f, field_name + '[filter_type]');
    var month_field = getFormElement(f, field_name + '[Month]');
    var day_field = getFormElement(f, field_name + '[Day]');
    var year_field = getFormElement(f, field_name + '[Year]');
    var month_end_field = getFormElement(f, field_name + '_end[Month]');
    var day_end_field = getFormElement(f, field_name + '_end[Day]');
    var year_end_field = getFormElement(f, field_name + '_end[Year]');
    if (checkbox.checked) {
        var bool = false;
    } else {
        var bool = true;
    }
    filter_type.disabled = bool;
    month_field.disabled = bool;
    day_field.disabled = bool;
    year_field.disabled = bool;
    month_end_field.disabled = bool;
    day_end_field.disabled = bool;
    year_end_field.disabled = bool;
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
    selectOption(this.document.wfl_form1, field_name + '[Day]', day);
    selectOption(this.document.wfl_form1, field_name + '[Month]', month);
    selectOption(this.document.wfl_form1, field_name + '[Year]', year);
}

function insertAfter(newElement,targetElement) {
    //target is what you want it to go after. Look for this elements parent.
    var parent = targetElement.parentNode;
    //if the parents lastchild is the targetElement...
    if(parent.lastchild == targetElement) {
        //add the newElement after the target element.
        parent.appendChild(newElement);
    } else {
        // else the target has siblings, insert the new element between the target and it's next sibling.
        parent.insertBefore(newElement, targetElement.nextSibling);
    }
}

function createTextBox(xsdmf_id, loop_num, name, limit, axsdmf_id, aname, attachSuggest, is_editor) {

    var trID = "tr_xsd_display_fields_" +xsdmf_id+ "_" + loop_num;
    var textboxID = "xsd_display_fields_" +xsdmf_id+ "_" + loop_num;
    prevElem = document.getElementById(trID);
    currElem = document.getElementById(textboxID);

    if(currElem.value == "")
        return;

    var row = document.createElement("tr");
    var td1 = document.createElement("td");
    var td2 = document.createElement("td");
    var textbox = document.createElement("input");
    var bold = document.createElement("b");

    if(loop_num == limit) {
        currElem.onkeyup = null;
        currElem.onfocus = null;
        currElem.onchange = null;
        return;
    }

    loop_num++;

    /*
     * Create our up and down images
     * for swapping textbox content
     */
    var uparrow=document.createElement('img');
    uparrow.src = rel_url+"images/uparrow.png";
    uparrow.style.cursor = "pointer";
    uparrow.onclick = function () {swapTextBox('xsd_display_fields_'+xsdmf_id, axsdmf_id, loop_num, -1)};

    var downarrow=document.createElement('img');
    downarrow.src = rel_url+"images/downarrow.png";
    downarrow.id = "xsd_display_fields_"+xsdmf_id+"_"+loop_num+"_arrow";
    downarrow.style.cursor = "pointer";
    downarrow.style.display = "none";
    downarrow.onclick = function () {swapTextBox('xsd_display_fields_'+xsdmf_id, axsdmf_id, loop_num, 1)};

    row.setAttribute("id", "tr_xsd_display_fields_" + xsdmf_id + "_" +loop_num);
    row.className = "default";
    td1.setAttribute("bgColor",cell_color);

    textbox.id = "xsd_display_fields_"+xsdmf_id+"_"+loop_num;
    textbox.type = "text";
    textbox.name = "xsd_display_fields["+xsdmf_id+"]["+loop_num+"]";
    textbox.size = "50";
    if (loop_num % 2 == 0) {
        td2.className = "default text-input-even";
    } else {
        td2.className = "default text-input-odd";
    }

    textbox.onkeyup = function () {createTextBox(xsdmf_id,loop_num,name,limit,axsdmf_id,aname, attachSuggest, is_editor)};
    textbox.onchange = function () {createTextBox(xsdmf_id,loop_num,name,limit,axsdmf_id,aname, attachSuggest, is_editor)};
    textbox.onfocus = function () {createTextBox(xsdmf_id,loop_num,name,limit,axsdmf_id,aname, attachSuggest, is_editor)};

    bold.appendChild(document.createTextNode(name +" "+ (loop_num+1)));
    td1.appendChild(bold);
    td1.appendChild(uparrow);
    td1.appendChild(downarrow);
    row.appendChild(td1);
    row.appendChild(td2);
    td2.appendChild(textbox);

    insertAfter(row, prevElem);

    if((axsdmf_id != '') && (is_editor == 1)) {
       createAuthorSuggest(td2, xsdmf_id, axsdmf_id, aname, loop_num);
    }

    if(attachSuggest == 1) {
        createGeneralSuggest(td2, xsdmf_id, '', loop_num);
    }

    /*
     * Now we've created our new textbox, find the textbox row
     * above and unhide its down arrow
     */
    var show_arrow = document.getElementById('xsd_display_fields_' + xsdmf_id + '_' + (loop_num-1)+'_arrow');
    if (show_arrow != null) {
        show_arrow.style.display = 'inline';
    }

    currElem.onkeyup = null;
    currElem.onfocus = null;
    currElem.onchange = null;
}

/*
 * Create YUI author suggest control textboxes
 */
function createAuthorSuggest(td, xsdmf_id, axsdmf_id, name, loop_num) {
    var table = document.createElement("table");
    var tbody = document.createElement("tbody");
    var row = document.createElement("tr");
    var td1 = document.createElement("td");
    var td2 = document.createElement("td");
    var bold = document.createElement("b");

    // selectbox to hold the author id
    var select = document.createElement("select");
    select.id = "xsd_display_fields_"+axsdmf_id+"_"+loop_num;
    select.name = "xsd_display_fields_"+axsdmf_id+"_"+loop_num;

    var option = document.createElement("option");
    option.value = "0";
    option.text = "(none)";

    try {
      select.add(option, null); // standards compliant; doesn't work in IE
    }
    catch(ex) {
      select.add(option, 0); // IE only
    }

    var div1 = document.createElement("div");
    div1.id = "authorsuggest";
    div1.style.width = "15em";
    div1.style.height = "2em";
    div1.style.position = "relative";

    // The div container that shows the results
    var div2 = document.createElement("div");
    div2.id = "xsd_display_fields_"+axsdmf_id+"_"+loop_num+"_container";

    // The textbox the user searches in
    var input = document.createElement("input");
    input.type = "text";
    input.id = "xsd_display_fields_"+axsdmf_id+"_"+loop_num+"_lookup";

    div2.appendChild(input);
    div1.appendChild(div2);

    bold.appendChild(document.createTextNode(name + ": "));
    td1.appendChild(bold);
    td1.appendChild(select);
    td2.appendChild(div1);
    row.appendChild(td1);
    row.appendChild(td2);
    tbody.appendChild(row);
    table.appendChild(tbody);
    td.appendChild(table);

    attachYuiAuthorSuggest(axsdmf_id, xsdmf_id, loop_num);
}

/*
 * Create YUI conference suggest control textboxes
 */
function createConferenceSuggest(td, xsdmf_id, axsdmf_id, name, loop_num) {
    var table = document.createElement("table");
    var tbody = document.createElement("tbody");
    var row = document.createElement("tr");
    var td1 = document.createElement("td");
    var td2 = document.createElement("td");
    var bold = document.createElement("b");

    // selectbox to hold the conference id
    var select = document.createElement("select");
    select.id = "xsd_display_fields_"+axsdmf_id+"_"+loop_num;
    select.name = "xsd_display_fields_"+axsdmf_id+"_"+loop_num;

    var option = document.createElement("option");
    option.value = "0";
    option.text = "(none)";

    try {
      select.add(option, null); // standards compliant; doesn't work in IE
    }
    catch(ex) {
      select.add(option, 0); // IE only
    }

    var div1 = document.createElement("div");
    div1.id = "conferencesuggest";
    div1.style.width = "15em";
    div1.style.height = "2em";
    div1.style.position = "relative";

    // The div container that shows the results
    var div2 = document.createElement("div");
    div2.id = "xsd_display_fields_"+axsdmf_id+"_"+loop_num+"_container";

    // The textbox the user searches in
    var input = document.createElement("input");
    input.type = "text";
    input.id = "xsd_display_fields_"+axsdmf_id+"_"+loop_num+"_lookup";

    div2.appendChild(input);
    div1.appendChild(div2);

    bold.appendChild(document.createTextNode(name + ": "));
    td1.appendChild(bold);
    td1.appendChild(select);
    td2.appendChild(div1);
    row.appendChild(td1);
    row.appendChild(td2);
    tbody.appendChild(row);
    table.appendChild(tbody);
    td.appendChild(table);

    attachYuiConferenceSuggest(axsdmf_id, xsdmf_id, loop_num);
}

/*
 * Create YUI publisher suggest control textboxes
 */
function createPublisherSuggest(td, xsdmf_id, axsdmf_id, name, loop_num) {
    var table = document.createElement("table");
    var tbody = document.createElement("tbody");
    var row = document.createElement("tr");
    var td1 = document.createElement("td");
    var td2 = document.createElement("td");
    var bold = document.createElement("b");

    // selectbox to hold the publisher id
    var select = document.createElement("select");
    select.id = "xsd_display_fields_"+axsdmf_id+"_"+loop_num;
    select.name = "xsd_display_fields_"+axsdmf_id+"_"+loop_num;

    var option = document.createElement("option");
    option.value = "0";
    option.text = "(none)";

    try {
      select.add(option, null); // standards compliant; doesn't work in IE
    }
    catch(ex) {
      select.add(option, 0); // IE only
    }

    var div1 = document.createElement("div");
    div1.id = "publishersuggest";
    div1.style.width = "15em";
    div1.style.height = "2em";
    div1.style.position = "relative";

    // The div container that shows the results
    var div2 = document.createElement("div");
    div2.id = "xsd_display_fields_"+axsdmf_id+"_"+loop_num+"_container";

    // The textbox the user searches in
    var input = document.createElement("input");
    input.type = "text";
    input.id = "xsd_display_fields_"+axsdmf_id+"_"+loop_num+"_lookup";

    div2.appendChild(input);
    div1.appendChild(div2);

    bold.appendChild(document.createTextNode(name + ": "));
    td1.appendChild(bold);
    td1.appendChild(select);
    td2.appendChild(div1);
    row.appendChild(td1);
    row.appendChild(td2);
    tbody.appendChild(row);
    table.appendChild(tbody);
    td.appendChild(table);

    attachYuiPublisherSuggest(axsdmf_id, xsdmf_id, loop_num);
}

function createCustomVocabSuggest(xsdmf_id, axsdmf_id, name, loop_num, limit) {


    var trID = "tr_xsd_display_fields_" +xsdmf_id+ "_" + loop_num;
    var textboxID = "xsd_display_fields_" +xsdmf_id+ "_" + loop_num;
    prevElem = document.getElementById(trID);
    currElem = document.getElementById(textboxID);

    if(currElem.value == "")
        return;

    var table = document.createElement("table");
    var tbody = document.createElement("tbody");
    var row = document.createElement("tr");
    var td1 = document.createElement("td");
    var td2 = document.createElement("td");
    var textbox = document.createElement("input");
    var bold = document.createElement("b");

    if(loop_num == limit) {
        currElem.onkeyup = null;
        currElem.onfocus = null;
        currElem.onchange = null;
        return;
    }

    loop_num++;

    /*
     * Create our up and down images
     * for swapping textbox content
     */
    var uparrow=document.createElement('img');
    uparrow.src = rel_url+"images/uparrow.png";
    uparrow.style.cursor = "pointer";
    uparrow.onclick = function () {swapDropDowns("xsd_display_fields_"+xsdmf_id+"_"+loop_num, "xsd_display_fields_"+xsdmf_id+"_"+(loop_num-1))};

    var downarrow=document.createElement('img');
    downarrow.src = rel_url+"images/downarrow.png";
    downarrow.id = "xsd_display_fields_"+xsdmf_id+"_"+loop_num+"_arrow";
    downarrow.style.cursor = "pointer";
    downarrow.style.display = "none";


    downarrow.onclick = function () {swapDropDowns("xsd_display_fields_"+xsdmf_id+"_"+loop_num, "xsd_display_fields_"+xsdmf_id+"_"+(loop_num+1))};

    row.setAttribute("id", "tr_xsd_display_fields_" + xsdmf_id + "_" +loop_num);
    row.className = "default";
    td1.setAttribute("bgColor",cell_color);
    td2.setAttribute("bgColor",value_color);
    td2.style.whiteSpace="nowrap";
    // selectbox to hold the author id
    var select = document.createElement("select");
    select.id = "xsd_display_fields_"+axsdmf_id+"_"+loop_num;
    select.name = "xsd_display_fields["+axsdmf_id+"]["+loop_num+"]";

    var option = document.createElement("option");
    option.value = "";
    option.text = "(none)";

    try {
      select.add(option, null); // standards compliant; doesn't work in IE
    }
    catch(ex) {
      select.add(option, 0); // IE only
    }

    select.onkeyup = function () {createCustomVocabSuggest(xsdmf_id,axsdmf_id,name,loop_num,limit)};
    select.onchange = function () {createCustomVocabSuggest(xsdmf_id,axsdmf_id,name,loop_num,limit)};
    select.onfocus = function () {createCustomVocabSuggest(xsdmf_id,axsdmf_id,name,loop_num,limit)};
	select.style.width="300px";
	select.style.cssFloat="left";
    select.className="default";
    var div1 = document.createElement("div");
    div1.id = "customsuggest";
    div1.style.width = "30em";
    div1.style.height = "2em";
    div1.style.position = "relative";
	div1.style.cssFloat = "left";

    // The div container that shows the results
    var div2 = document.createElement("div");
    div2.id = "xsd_display_fields_"+axsdmf_id+"_"+loop_num+"_container";
    div2.style.position="absolute";

    // The textbox the user searches in
    var input = document.createElement("input");
    input.type = "text";
    input.id = "xsd_display_fields_"+axsdmf_id+"_"+loop_num+"_lookup";

    div2.appendChild(input);
    div1.appendChild(div2);
    td1.appendChild(uparrow);
    td1.appendChild(downarrow);

    bold.appendChild(document.createTextNode(name + ": "));
    td1.appendChild(bold);
    td2.appendChild(select);
    td2.appendChild(div1);
    row.appendChild(td1);
    row.appendChild(td2);
//    tbody.appendChild(row);
//    table.appendChild(tbody);
//    td.appendChild(table);

    insertAfter(row, prevElem);
    attachYuiGeneralSuggest(xsdmf_id, xsdmf_id, loop_num);
//    createGeneralSuggest(td2, xsdmf_id, '', loop_num);
//    attachGeneralSuggest(axsdmf_id, xsdmf_id, loop_num);


/*
	 * Now we've created our new textbox, find the textbox row
	 * above and unhide its down arrow
	 */
	var show_arrow = document.getElementById('xsd_display_fields_' + xsdmf_id + '_' + (loop_num-1)+'_arrow');
	if (show_arrow != null) {
	    show_arrow.style.display = 'inline';
	}

	currElem.onkeyup = null;
	currElem.onfocus = null;
	currElem.onchange = null;


}

function createGeneralSuggest(td, xsdmf_id, to_fill_xsdmf_id, loop_num) {
    var div1 = document.createElement("div");
    div1.id = "generalsuggest";
    div1.style.width = "15em";
    div1.style.height = "2em";
    div1.style.position = "relative";

    var div2 = document.createElement("div");
    div2.id = xsdmf_id+"_"+loop_num+"_container";

    div1.appendChild(div2);
    td.appendChild(div1);

    attachYuiGeneralSuggest(xsdmf_id, to_fill_xsdmf_id, loop_num);
}

function formatAuthorRes(oResultItem, sQuery) {
    var usernameTxt = "";
    if( oResultItem[1].username != "" && oResultItem[1].username != null ) {
            usernameTxt = ' (' +  oResultItem[1].username + ')'
    }
    return oResultItem[1].name + usernameTxt;
}

function formatPIDRes(oResultItem, sQuery) {
    var pidTxt = "";
    if( oResultItem[1].pid != "" && oResultItem[1].pid != null ) {
        pidTxt = ' (' +  oResultItem[1].pid + ')'
    }
    return oResultItem[1].name + pidTxt;
}

function formatConferenceRes(oResultItem, sQuery) {
    var idTxt = "";
    if( oResultItem[1].id != "" && oResultItem[1].id != null ) {
            idTxt = ' (' +  oResultItem[1].id + ')'
    }
    return oResultItem[1].name + idTxt;
}

function formatPublisherRes(oResultItem, sQuery) {
    var idTxt = "";
    if( oResultItem[1].id != "" && oResultItem[1].id != null ) {
            idTxt = ' (' +  oResultItem[1].id + ')'
    }
    return oResultItem[1].name + idTxt;
}

function attachYuiAuthorSuggest(axsdmf_id, xsdmf_id, loop_num)
{
    autocomp = new YAHOO.widget.AutoComplete("xsd_display_fields_"+axsdmf_id+"_"+loop_num+"_lookup","xsd_display_fields_"+axsdmf_id+"_"+loop_num+"_container", myDataSourceAuthor);
    autocomp.maxResultsDisplayed = 60;
    autocomp.formatResult = formatAuthorRes;
    autocomp.registerControls(document.getElementById("xsd_display_fields_"+xsdmf_id+"_"+loop_num), document.getElementById("xsd_display_fields_"+axsdmf_id+"_"+loop_num));
    autocomp.textboxFocusEvent.subscribe(function(){
        var sInputValue = YAHOO.util.Dom.get("xsd_display_fields_"+axsdmf_id+"_"+loop_num+"_lookup").value;
        if(sInputValue.length === 0) {
            var oSelf = this;
            setTimeout(function(){oSelf.sendQuery(sInputValue);},0);
        }
    });
}

function attachYuiPIDSuggest(axsdmf_id, xsdmf_id, loop_num)
{
    autocomp = new YAHOO.widget.AutoComplete("xsd_display_fields_"+axsdmf_id+"_"+loop_num+"_lookup","xsd_display_fields_"+axsdmf_id+"_"+loop_num+"_container", myDataSourcePID);
    autocomp.maxResultsDisplayed = 60;
    autocomp.formatResult = formatPIDRes;
    autocomp.registerControls(document.getElementById("xsd_display_fields_"+xsdmf_id+"_"+loop_num), document.getElementById("xsd_display_fields_"+axsdmf_id+"_"+loop_num));
    autocomp.textboxFocusEvent.subscribe(function(){
        var sInputValue = YAHOO.util.Dom.get("xsd_display_fields_"+axsdmf_id+"_"+loop_num+"_lookup").value;
        if(sInputValue.length === 0) {
            var oSelf = this;
            setTimeout(function(){oSelf.sendQuery(sInputValue);},0);
        }
    });
}

function attachYuiConferenceSuggest(axsdmf_id, xsdmf_id, loop_num)
{
    autocomp = new YAHOO.widget.AutoComplete("xsd_display_fields_"+axsdmf_id+"_"+loop_num+"_lookup","xsd_display_fields_"+axsdmf_id+"_"+loop_num+"_container", myDataSourceConference);
    autocomp.maxResultsDisplayed = 60;
    autocomp.formatResult = formatConferenceRes;
    autocomp.registerControls(document.getElementById("xsd_display_fields_"+xsdmf_id+"_"+loop_num), document.getElementById("xsd_display_fields_"+axsdmf_id+"_"+loop_num));
    autocomp.textboxFocusEvent.subscribe(function(){
        var sInputValue = YAHOO.util.Dom.get("xsd_display_fields_"+axsdmf_id+"_"+loop_num+"_lookup").value;
        if(sInputValue.length === 0) {
            var oSelf = this;
            setTimeout(function(){oSelf.sendQuery(sInputValue);},0);
        }
    });
}

function attachYuiPublisherSuggest(axsdmf_id, xsdmf_id, loop_num)
{
    autocomp = new YAHOO.widget.AutoComplete("xsd_display_fields_"+axsdmf_id+"_"+loop_num+"_lookup","xsd_display_fields_"+axsdmf_id+"_"+loop_num+"_container", myDataSourcePublisher);
    autocomp.maxResultsDisplayed = 60;
    autocomp.formatResult = formatPublisherRes;
    autocomp.registerControls(document.getElementById("xsd_display_fields_"+xsdmf_id+"_"+loop_num), document.getElementById("xsd_display_fields_"+axsdmf_id+"_"+loop_num));
    autocomp.textboxFocusEvent.subscribe(function(){
        var sInputValue = YAHOO.util.Dom.get("xsd_display_fields_"+axsdmf_id+"_"+loop_num+"_lookup").value;
        if(sInputValue.length === 0) {
            var oSelf = this;
            setTimeout(function(){oSelf.sendQuery(sInputValue);},0);
        }
    });
}

function attachYuiGeneralSuggest(xsdmf_id, to_fill_xsdmf_id, loop_num)
{
    var myDataSource = new YAHOO.widget.DS_XHR(myServer, mySchema);
    myDataSource.scriptQueryAppend = "xsdmf_id=" +xsdmf_id;

    // Instantiate first AutoComplete
	if (to_fill_xsdmf_id != '') {
    	oAutoComp = new YAHOO.widget.AutoComplete("xsd_display_fields_"+xsdmf_id+"_"+loop_num+"_lookup", "xsd_display_fields_"+xsdmf_id+"_"+loop_num+"_container", myDataSource);
	} else {
//		oAutoComp = new YAHOO.widget.AutoComplete("xsd_display_fields_"+xsdmf_id+"_"+loop_num, "xsd_display_fields_"+xsdmf_id+"_"+loop_num, myDataSource);
	    oAutoComp = new YAHOO.widget.AutoComplete("xsd_display_fields_"+xsdmf_id+"_"+loop_num, xsdmf_id+"_"+loop_num+"_container", myDataSource);
	}
    oAutoComp.maxResultsDisplayed = 10;
    oAutoComp.formatResult = function(oResultItem, sQuery) {
        return oResultItem[1].name;
    };
	if (to_fill_xsdmf_id != '') {
           if (to_fill_xsdmf_id == xsdmf_id) {
		oAutoComp.registerControls(document.getElementById("xsd_display_fields_"+xsdmf_id+"_"+loop_num+"_lookup"), document.getElementById("xsd_display_fields_"+xsdmf_id+"_"+loop_num));
		//oAutoComp.registerControls(document.getElementById("xsd_display_fields_"+to_fill_xsdmf_id+"_"+loop_num));
           } else {
		oAutoComp.registerControls(document.getElementById("xsd_display_fields_"+xsdmf_id+"_"+loop_num), document.getElementById("xsd_display_fields_"+to_fill_xsdmf_id+"_"+loop_num));
           }
	}
    oAutoComp.textboxFocusEvent.subscribe(function(){
		if (to_fill_xsdmf_id != '') {
        	var sInputValue = YAHOO.util.Dom.get("xsd_display_fields_"+xsdmf_id+"_"+loop_num+"_lookup").value;
		} else {
        	var sInputValue = YAHOO.util.Dom.get("xsd_display_fields_"+xsdmf_id+"_"+loop_num).value;
		}
        if(sInputValue.length === 0) {
            var oSelf = this;
            setTimeout(function(){oSelf.sendQuery(sInputValue);},0);
        }
    });
}

// we want to replace the file description with a text box that will be submitted with the form
function editFileLabel(pid, filename, counter) {

	// set up the html to replace
	var spanName = pid+'_'+counter+'_span';
	var divName = pid+'_'+counter+'_div';
	var originalText = dojo.byId(spanName).innerHTML;
	var html = '<input type="text" class="default" id="'+pid+'_'+counter+'_input" name="editedFileDescriptions['+counter+'][newLabel]" value="'+originalText+'" maxlength="250">';
	html = html+'<input type="hidden" name="editedFileDescriptions['+counter+'][pid]" value="'+pid+'" >';
	html = html+'<input type="hidden" name="editedFileDescriptions['+counter+'][filename]" value="'+filename+'" >';

	// and do the replace
	dojo.byId(divName).innerHTML = html;
}

function editFilename(pid, filename, counter) {
	var newFilename = prompt("Please enter the new filename", filename);
	newFilename = trim(newFilename);
	// if there was no filename specified, or it wasn't changed, then ignore
	if (trim(filename) == newFilename || newFilename == '' || newFilename == null)
		return;

	// do checks on the new filename (to make sure it conforms to standards)
	if (!isValidSolrFilename(newFilename)) {

		var alertMsg = 'We could not rename the file. Please check that the new name conforms to the following:\n';
		alertMsg = alertMsg+' - only upper or lowercase alphanumeric characters or underscores (a-z, A-Z, _ and 0-9 only)\n';
		alertMsg = alertMsg+' - with only numbers and lowercase characters in the file extension,\n';
		alertMsg = alertMsg+' - under 45 characters,\n';
		alertMsg = alertMsg+' - with only one file extension (one period (.) character) and \n';
		alertMsg = alertMsg+' - starting with a letter. Eg "s12345678_phd_thesis.pdf"';

		alert(alertMsg);
		return;
	}

	// and if all checks work out, then update the filename
	// and set an hidden form element so that the filename can be updated in the database
	var filenameSpan = pid+'_'+counter+'_filename';
	var newFilenameSpan = pid+'_'+counter+'_newFilename';
	var html = '<input type="hidden" name="editedFilenames['+counter+'][pid]" value="'+pid+'" >';
	html = html+'<input type="hidden" name="editedFilenames['+counter+'][originalFilename]" value="'+filename+'" >';
	html = html+'<input type="hidden" name="editedFilenames['+counter+'][newFilename]" value="'+newFilename+'" >';

	dojo.byId(filenameSpan).innerHTML = newFilename;
	dojo.byId(newFilenameSpan).innerHTML = html;
}