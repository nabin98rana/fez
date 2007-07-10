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
	// Currently won't show the text part of the vocab in the list as it needs to look that information up.
	// Could do an AJAX lookup on class.author.php or pass this info in from the calling javascript as an optional 
	// 'extra' param.
	// Will show the author list value for now.
	destinationList.options[len] = new Option(value, value); 
	destinationList.options[len].selected = true;
}


function copy_field_checkbox(dest,value)
{
	f = document.forms.wfl_form1;
	fe = getFormElement(f, dest);
	if (fe != null) {
		if (value == '' || value == null || value == 0) {
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
	var len = destinationList.length;
	// Currently won't show the text part of the vocab in the list as it needs to look that information up.
	// Could do an AJAX lookup on cv_selector.php or pass this info in from the calling javascript as an optional 
	// 'extra' param.
	// Will show the CV list value for now.
	destinationList.options[len] = new Option(value, value); 
	destinationList.options[len].selected = true;
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

function copy_field_text_area(dest, value)
{
	f = document.forms.wfl_form1;
	fe = getFormElement(f, dest);
	if (fe != null) {
		fe.value = value;
	}
}
