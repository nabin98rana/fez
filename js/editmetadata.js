var required_xsd_display_fields = new Array();
var xsd_display_fields = new Array();
var myDataSourceAuthor = new YAHOO.widget.DS_XHR(rel_url+"author_suggest_proxy.php", ["Result","name"]);

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

function createTextBox(xsdmf_id, loop_num, name, limit, axsdmf_id, aname, attachSuggest) {

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
    td2.setAttribute("bgColor",value_color);
    
    textbox.id = "xsd_display_fields_"+xsdmf_id+"_"+loop_num;
    textbox.type = "text";
    textbox.name = "xsd_display_fields["+xsdmf_id+"]["+loop_num+"]";
    textbox.size = "50";
    textbox.className = "default";
    
    textbox.onkeyup = function () {createTextBox(xsdmf_id,loop_num,name,limit,axsdmf_id,aname, attachSuggest)};
    textbox.onchange = function () {createTextBox(xsdmf_id,loop_num,name,limit,axsdmf_id,aname, attachSuggest)};
    textbox.onfocus = function () {createTextBox(xsdmf_id,loop_num,name,limit,axsdmf_id,aname, attachSuggest)};
    
    bold.appendChild(document.createTextNode(name +" "+ (loop_num+1)));
    td1.appendChild(bold);
    td1.appendChild(uparrow);
    td1.appendChild(downarrow);
    row.appendChild(td1);
    row.appendChild(td2);
    td2.appendChild(textbox);
    
    insertAfter(row, prevElem);
    
    if(axsdmf_id != '') {
       createAuthorSuggest(td2, xsdmf_id, axsdmf_id, aname, loop_num);
    }
    
    if(attachSuggest == 1) {
        createGeneralSuggest(td2, xsdmf_id, loop_num);
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

function createGeneralSuggest(td, xsdmf_id, loop_num) {
    var div1 = document.createElement("div");
    div1.id = "generalsuggest";
    div1.style.width = "15em";
    div1.style.height = "2em";
    div1.style.position = "relative";
    
    var div2 = document.createElement("div");
    div2.id = xsdmf_id+"_"+loop_num+"_container";
    
    div1.appendChild(div2);
    td.appendChild(div1);
   
    attachYuiGeneralSuggest(xsdmf_id, loop_num);
}

function formatAuthorRes(oResultItem, sQuery) {
    var usernameTxt = "";
    if( oResultItem[1].username != "" && oResultItem[1].username != null ) {
            usernameTxt = ' (' +  oResultItem[1].username + ')'
    }
    return oResultItem[1].name + usernameTxt;
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

function attachYuiGeneralSuggest(xsdmf_id, loop_num) 
{
    var myDataSource = new YAHOO.widget.DS_XHR(myServer, mySchema);
    myDataSource.scriptQueryAppend = "xsdmf_id=" +xsdmf_id;

    // Instantiate first AutoComplete
    oAutoComp = new YAHOO.widget.AutoComplete("xsd_display_fields_"+xsdmf_id+"_"+loop_num, xsdmf_id+"_"+loop_num+"_container", myDataSource);
    oAutoComp.maxResultsDisplayed = 10;
    oAutoComp.formatResult = function(oResultItem, sQuery) {
        return oResultItem[1].name;
    };
    oAutoComp.textboxFocusEvent.subscribe(function(){
        var sInputValue = YAHOO.util.Dom.get("xsd_display_fields_"+xsdmf_id+"_"+loop_num).value;
        if(sInputValue.length === 0) {
            var oSelf = this;
            setTimeout(function(){oSelf.sendQuery(sInputValue);},0);
        }
    });
}
