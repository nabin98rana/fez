function copy_field_author_selector(dest,value)
{
	f = document.forms.wfl_form1;
	selectOption(f, dest, value);
}

function copy_field_author_suggestor(dest,value)
{
	f = document.forms.wfl_form1;
	destinationList = getFormElement(f, dest);
	var len = destinationList.length;
	// get the label for the author using an ajax call
	aut = new Author;
	aut.onGetDisplayNameError = function() {
		destinationList.options[len] = new Option(value, value); 
		destinationList.options[len].selected = true;
	}
	aut.getDisplayName(value, function(res) {
		destinationList.options[len] = new Option(res + ' (' + value + ')', value); 
		destinationList.options[len].selected = true;
	}
	);

}


function copy_field_checkbox(dest,value)
{
	f = document.forms.wfl_form1;
	fe = getFormElement(f, dest);
	if (fe != null) {
		if (value == '' || value == null || value == 0 || value == 'off' || value == 'no') {
			fe.checked = false;
		} else {
			fe.checked = true;
		}
	}
}

function copy_field_combo(dest,value)
{
	f = document.forms.wfl_form1;
	selectOption(f, dest, value);
}

function copy_field_contvocab_selector(dest,value)
{
	f = document.forms.wfl_form1;
	destinationList = getFormElement(f, dest);
	if (!optionValueExists(destinationList, value)) {
		var len = destinationList.length;
		// get the label for the controlled vocab using an ajax call
		cv = new Controlled_Vocab;
		cv.onGetTitleError = function() {
			destinationList.options[len] = new Option(value, value); 
			destinationList.options[len].selected = true;
		}
		cv.getTitle(value, function(res) {
			destinationList.options[len] = new Option(res, value); 
			destinationList.options[len].selected = true;
		}
		);
	}
}

function copy_field_date(dest,value)
{
	f = document.forms.wfl_form1;
	selectOption(f, dest + '[Month]', value.substr(5,2));
	selectOption(f, dest + '[Day]', parseInt(value.substr(8,2))).toString;  // remove leading zero
	selectOption(f, dest + '[Year]', value.substr(0,4));
}

function copy_field_multiple(dest,value)
{
	f = document.forms.wfl_form1;
	selectOption(f, dest, value);
}

function copy_field_org_selector(dest,value)
{
	f = document.forms.wfl_form1;
	selectOption(f, dest, value);
}

function copy_field_text(dest, value)
{
	f = document.forms.wfl_form1;
	fe = getFormElement(f, dest);
	if (fe != null) {
		fe.value = value;
	}
}

function copy_field_textarea(dest, value)
{
	f = document.forms.wfl_form1;
	fe = getFormElement(f, dest);
	if (fe != null) {
		fe.value = value;
	}
}

function unHideCompareRows()
{
	elems = document.getElementsByTagName('span');
	for (ii = 0; ii < elems.length; ii++) {
		if (elems[ii].className == 'right_value') {
			parent_row = getParentRow(elems[ii]);
			parent_row.style.display='';
		}
	}
}

function getParentRow(elem)
{
	var res;
	for (res = elem.parentNode; res != null && res.tagName != 'TR'; res = res.parentNode) {
		;// hello
	}
	return res;
}
